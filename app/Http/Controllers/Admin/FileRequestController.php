<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeFileRequest;
use App\Models\EmployeeFileTemplate;
use App\Models\User;
use App\Support\EmployeeFileTemplateRenderer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FileRequestController extends Controller
{
    public function __construct(
        private readonly EmployeeFileTemplateRenderer $renderer
    ) {}

    public function index(Request $request)
    {
        $this->authorizeAccess();

        $templates = EmployeeFileTemplate::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $employees = $this->scopedEmployeeQuery()
            ->with(['department:id,name'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'department_id', 'role']);

        $recentRequests = EmployeeFileRequest::query()
            ->with(['template:id,name,category', 'employee:id,name,email', 'generator:id,name'])
            ->whereHas('employee', function ($query) {
                $this->applyEmployeeScope($query);
            })
            ->latest()
            ->paginate(15)
            ->appends($request->query());

        return view('admin.employee-management.file-request.index', compact(
            'templates',
            'employees',
            'recentRequests'
        ));
    }

    public function templatesIndex()
    {
        $this->authorizeAccess();

        $templates = EmployeeFileTemplate::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.employee-management.file-request.templates.index', compact('templates'));
    }

    public function templatesCreate()
    {
        $this->authorizeAccess();

        return view('admin.employee-management.file-request.templates.form', [
            'template' => new EmployeeFileTemplate([
                'is_active' => true,
                'category' => 'letter',
                'sort_order' => 0,
            ]),
            'categories' => EmployeeFileTemplate::CATEGORIES,
            'placeholders' => $this->renderer->availablePlaceholders(),
        ]);
    }

    public function templatesStore(Request $request)
    {
        $this->authorizeAccess();

        $validated = $this->validateTemplate($request);
        $validated['custom_fields'] = $this->parseCustomFields($request);

        EmployeeFileTemplate::create($validated);

        return redirect()
            ->route('admin.file-request.templates.index')
            ->with('success', 'Template created successfully.');
    }

    public function templatesEdit(EmployeeFileTemplate $template)
    {
        $this->authorizeAccess();

        return view('admin.employee-management.file-request.templates.form', [
            'template' => $template,
            'categories' => EmployeeFileTemplate::CATEGORIES,
            'placeholders' => $this->renderer->availablePlaceholders($template),
        ]);
    }

    public function templatesUpdate(Request $request, EmployeeFileTemplate $template)
    {
        $this->authorizeAccess();

        $validated = $this->validateTemplate($request, $template);
        $validated['custom_fields'] = $this->parseCustomFields($request);

        $template->update($validated);

        return redirect()
            ->route('admin.file-request.templates.index')
            ->with('success', 'Template updated successfully.');
    }

    public function templatesDestroy(EmployeeFileTemplate $template)
    {
        $this->authorizeAccess();

        $template->delete();

        return redirect()
            ->route('admin.file-request.templates.index')
            ->with('success', 'Template deleted successfully.');
    }

    public function preview(Request $request)
    {
        $this->authorizeAccess();

        [$template, $employee, $fieldValues] = $this->resolveGenerationInput($request);
        $html = $this->renderer->render($template, $employee, $fieldValues);

        return view('admin.employee-management.file-request.preview', [
            'title' => $template->name . ' — ' . $employee->name,
            'html' => $html,
        ]);
    }

    public function generate(Request $request)
    {
        $this->authorizeAccess();

        [$template, $employee, $fieldValues] = $this->resolveGenerationInput($request);
        $html = $this->renderer->render($template, $employee, $fieldValues);

        $fileRequest = EmployeeFileRequest::create([
            'employee_file_template_id' => $template->id,
            'user_id' => $employee->id,
            'generated_by' => auth()->id(),
            'title' => $template->name . ' — ' . $employee->name,
            'field_values' => $fieldValues,
            'rendered_html' => $html,
        ]);

        $pdf = $this->loadFileRequestPdf($fileRequest->title, $html, $template);

        $filename = Str::slug($template->slug . '-' . $employee->name . '-' . now()->format('Y-m-d')) . '.pdf';
        $disk = 'digitalocean';
        $root = trim((string) env('DIGITALOCEAN_SPACES_ROOT_PATH', ''), '/');
        $dir = $root ? $root . '/employee-file-requests' : 'employee-file-requests';
        $path = $dir . '/' . $fileRequest->id . '/' . $filename;

        $pdfContent = $pdf->output();

        try {
            Storage::disk($disk)->put($path, $pdfContent);
            $fileRequest->update(['pdf_path' => $path]);
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->route('admin.file-request.view', $fileRequest);
    }

    public function view(EmployeeFileRequest $fileRequest)
    {
        $this->authorizeAccess();

        $fileRequest->loadMissing('employee');
        if (!$this->canAccessEmployee($fileRequest->employee)) {
            abort(403);
        }

        $filename = $fileRequest->pdf_path
            ? basename($fileRequest->pdf_path)
            : Str::slug($fileRequest->title) . '.pdf';

        if ($this->shouldServeStoredPdf($fileRequest)) {
            return response(Storage::disk('digitalocean')->get($fileRequest->pdf_path), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]);
        }

        return $this->streamRenderedPdf($fileRequest, $filename);
    }

    public function download(EmployeeFileRequest $fileRequest)
    {
        $this->authorizeAccess();

        $fileRequest->loadMissing('employee');
        if (!$this->canAccessEmployee($fileRequest->employee)) {
            abort(403);
        }

        if ($this->shouldServeStoredPdf($fileRequest)) {
            return Storage::disk('digitalocean')->download(
                $fileRequest->pdf_path,
                basename($fileRequest->pdf_path)
            );
        }

        $fileRequest->loadMissing(['template', 'employee']);

        return $this->streamRenderedPdf($fileRequest, Str::slug($fileRequest->title) . '.pdf', true);
    }

    public function destroy(EmployeeFileRequest $fileRequest)
    {
        $this->authorizeAccess();

        $fileRequest->loadMissing('employee');
        if (!$this->canAccessEmployee($fileRequest->employee)) {
            abort(403);
        }

        if ($fileRequest->pdf_path) {
            Storage::disk('digitalocean')->delete($fileRequest->pdf_path);
        }

        $fileRequest->delete();

        return redirect()
            ->route('admin.file-request.index')
            ->with('success', 'Generated file record deleted.');
    }

    private function authorizeAccess(): void
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->canAccessEmployeeManagement()) {
            abort(403, 'Access denied. You do not have permission to access Employee Management.');
        }
    }

    private function scopedEmployeeQuery()
    {
        $authUser = auth()->user();
        $query = User::query()->where('role', 'employee');
        $allowedDepartmentIds = $authUser->canAccessEmployeeManagement()
            ? $authUser->getAllowedDepartmentIds()
            : null;

        if ($allowedDepartmentIds !== null) {
            $query->whereIn('department_id', $allowedDepartmentIds);
        }

        return $query;
    }

    private function applyEmployeeScope($query): void
    {
        $allowedDepartmentIds = auth()->user()->getAllowedDepartmentIds();
        if ($allowedDepartmentIds !== null) {
            $query->whereIn('department_id', $allowedDepartmentIds);
        }
    }

    private function canAccessEmployee(?User $employee): bool
    {
        if (!$employee || $employee->role !== 'employee') {
            return false;
        }

        $allowedDepartmentIds = auth()->user()->getAllowedDepartmentIds();
        if ($allowedDepartmentIds === null) {
            return true;
        }

        return in_array((int) $employee->department_id, $allowedDepartmentIds, true);
    }

    /**
     * @return array{0: EmployeeFileTemplate, 1: User, 2: array<string, mixed>}
     */
    private function resolveGenerationInput(Request $request): array
    {
        $validated = $request->validate([
            'template_id' => ['required', 'exists:employee_file_templates,id'],
            'employee_id' => ['required', 'exists:users,id'],
            'fields' => ['nullable', 'array'],
        ]);

        $template = EmployeeFileTemplate::query()
            ->where('is_active', true)
            ->findOrFail($validated['template_id']);

        $employee = $this->scopedEmployeeQuery()->findOrFail($validated['employee_id']);

        if ($template->isCertificateOfEmployment()) {
            $request->validate([
                'fields.job_position' => ['required', 'string', 'max:255'],
                'fields.employment_start' => ['required', 'string', 'max:100'],
            ], [
                'fields.job_position.required' => 'Job position is required for Certificate of Employment.',
                'fields.employment_start.required' => 'Employment start date is required (e.g. JULY 2025).',
            ]);
        }

        $fieldValues = [];
        foreach ($template->customFieldDefinitions() as $field) {
            $key = (string) $field['key'];
            $fieldValues[$key] = $this->renderer->resolveCustomFieldValue(
                $template,
                $field,
                $validated['fields'][$key] ?? null
            );
        }

        return [$template, $employee, $fieldValues];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateTemplate(Request $request, ?EmployeeFileTemplate $template = null): array
    {
        $slugRule = Rule::unique('employee_file_templates', 'slug');
        if ($template) {
            $slugRule = $slugRule->ignore($template->id);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', $slugRule],
            'category' => ['required', Rule::in(array_keys(EmployeeFileTemplate::CATEGORIES))],
            'description' => ['nullable', 'string', 'max:2000'],
            'body' => ['required', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = trim((string) ($validated['slug'] ?? '')) ?: Str::slug($request->input('name'));
        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = (int) $request->input('sort_order', 0);

        return $validated;
    }

    /**
     * @return list<array{key: string, label: string, default?: string}>
     */
    private function parseCustomFields(Request $request): array
    {
        $keys = (array) $request->input('custom_field_key', []);
        $labels = (array) $request->input('custom_field_label', []);
        $defaults = (array) $request->input('custom_field_default', []);
        $fields = [];

        foreach ($keys as $index => $key) {
            $key = trim((string) $key);
            if ($key === '') {
                continue;
            }

            $fields[] = [
                'key' => Str::slug($key, '_'),
                'label' => trim((string) ($labels[$index] ?? $key)),
                'default' => trim((string) ($defaults[$index] ?? '')),
            ];
        }

        return $fields;
    }

    private function shouldServeStoredPdf(EmployeeFileRequest $fileRequest): bool
    {
        if ($fileRequest->template?->isCertificateOfEmployment()) {
            return false;
        }

        return $fileRequest->pdf_path
            && Storage::disk('digitalocean')->exists($fileRequest->pdf_path);
    }

    private function streamRenderedPdf(
        EmployeeFileRequest $fileRequest,
        string $filename,
        bool $asDownload = false
    ) {
        $fileRequest->loadMissing(['template', 'employee']);

        $template = $fileRequest->template;
        $employee = $fileRequest->employee;

        $html = ($template && $employee)
            ? $this->renderer->render(
                $template,
                $employee,
                is_array($fileRequest->field_values) ? $fileRequest->field_values : []
            )
            : (string) ($fileRequest->rendered_html ?? '');

        $pdf = $this->loadFileRequestPdf($fileRequest->title, $html, $template);

        return $asDownload ? $pdf->download($filename) : $pdf->stream($filename);
    }

    private function loadFileRequestPdf(string $title, string $html, ?EmployeeFileTemplate $template)
    {
        return Pdf::loadView('admin.employee-management.file-request.pdf', [
            'title' => $title,
            'html' => $html,
            'fullBleed' => false,
        ])->setPaper('a4', 'portrait');
    }
}
