<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LinkedAccount;
use App\Models\Omada;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OmadaController extends Controller
{
    private function ensureFullAccess(): void
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403, 'Full admin access required to manage Omada.');
        }
    }

    public function index(Request $request)
    {
        $this->ensureFullAccess();

        $query = Omada::orderByDesc('created_at');

        $search = $request->input('search');
        if ($search && trim($search) !== '') {
            $term = '%' . trim($search) . '%';
            $query->where(function ($q) use ($term, $search) {
                $q->where('account_linked_email', 'like', $term)
                    ->orWhere('site', 'like', $term)
                    ->orWhere('office', 'like', $term)
                    ->orWhere('type', 'like', $term)
                    ->orWhere('serial_number', 'like', $term)
                    ->orWhere('mac_address', 'like', $term)
                    ->orWhere('license', 'like', $term);
                // Status is derived; match "active" / "expired" by license_expiration
                $lower = strtolower(trim($search));
                if ($lower === 'active') {
                    $q->orWhere(function ($q2) {
                        $q2->whereNotNull('license_expiration')->where('license_expiration', '>=', now()->startOfDay());
                    });
                } elseif ($lower === 'expired') {
                    $q->orWhere(function ($q2) {
                        $q2->whereNull('license_expiration')->orWhere('license_expiration', '<', now()->startOfDay());
                    });
                }
            });
        }

        $omadas = $query->paginate(15)->withQueryString();

        return view('admin.omadas.index', compact('omadas', 'search'));
    }

    public function show(Omada $omada)
    {
        $this->ensureFullAccess();

        if (request()->wantsJson() || request()->ajax()) {
            $data = $omada->toArray();
            $data['license_expiration_formatted'] = $omada->license_expiration ? $omada->license_expiration->format('M j, Y') : null;
            $data['status'] = $omada->status;
            return response()->json($data);
        }

        return redirect()->to('/admin/omadas');
    }

    public function create()
    {
        $this->ensureFullAccess();

        return view('admin.omadas.create');
    }

    public function store(Request $request)
    {
        $this->ensureFullAccess();

        $validated = $request->validate([
            'account_linked_email' => 'nullable|email|max:255',
            'site' => 'nullable|string|max:255',
            'office' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'mac_address' => 'nullable|string|max:255',
            'license' => 'nullable|string|max:255',
            'license_expiration' => 'nullable|date',
        ]);

        // Check if a device with the same Serial number or License already exists
        $deviceFields = [
            'serial_number' => 'Serial number',
            'license' => 'License',
        ];
        $duplicateErrors = [];
        foreach ($deviceFields as $field => $label) {
            $value = isset($validated[$field]) ? trim((string) $validated[$field]) : '';
            if ($value !== '' && Omada::where($field, $value)->exists()) {
                $duplicateErrors[$field] = "A device with this {$label} already exists.";
            }
        }
        if (! empty($duplicateErrors)) {
            throw ValidationException::withMessages($duplicateErrors);
        }

        // Ensure a linked account exists for the email so the dashboard shows data
        if (! empty($validated['account_linked_email'])) {
            LinkedAccount::firstOrCreate(
                ['email' => $validated['account_linked_email']],
                ['email' => $validated['account_linked_email']]
            );
        }

        Omada::create($validated);

        return redirect()->to('/admin/omadas')
            ->with('success', 'Omada device added successfully.');
    }

    public function edit(Omada $omada)
    {
        $this->ensureFullAccess();

        return view('admin.omadas.edit', compact('omada'));
    }

    public function update(Request $request, Omada $omada)
    {
        $this->ensureFullAccess();

        $validated = $request->validate([
            'account_linked_email' => 'nullable|email|max:255',
            'site' => 'nullable|string|max:255',
            'office' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'mac_address' => 'nullable|string|max:255',
            'license' => 'nullable|string|max:255',
            'license_expiration' => 'nullable|date',
        ]);

        // Ensure a linked account exists for the email so the dashboard shows data
        if (! empty($validated['account_linked_email'])) {
            LinkedAccount::firstOrCreate(
                ['email' => $validated['account_linked_email']],
                ['email' => $validated['account_linked_email']]
            );
        }

        $omada->update($validated);

        return redirect()->to('/admin/omadas')
            ->with('success', 'Omada device updated successfully.');
    }

    public function destroy(Omada $omada)
    {
        $this->ensureFullAccess();

        $omada->delete();

        return redirect()->to('/admin/omadas')
            ->with('success', 'Omada device removed.');
    }

    public function importForm()
    {
        $this->ensureFullAccess();

        return view('admin.omadas.import');
    }

    public function importTemplate(): StreamedResponse
    {
        $this->ensureFullAccess();

        $headers = [
            'account_linked_email',
            'site',
            'office',
            'type',
            'serial_number',
            'mac_address',
            'license',
            'license_expiration',
        ];

        return new StreamedResponse(function () use ($headers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            fputcsv($out, [
                'account@example.com',
                'Site A',
                'Main Office',
                'Controller',
                'SN-OMADA-001',
                '00:11:22:33:44:55',
                'Standard',
                '2025-12-31',
            ]);
            fclose($out);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="omadas_import_template.csv"',
        ]);
    }

    public function processImport(Request $request)
    {
        $this->ensureFullAccess();

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            return redirect()->to('/admin/omadas/import')
                ->with('error', 'Could not read the uploaded file.');
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            return redirect()->to('/admin/omadas/import')
                ->with('error', 'The CSV file is empty or invalid.');
        }

        $expected = ['account_linked_email', 'site', 'office', 'type', 'serial_number', 'mac_address', 'license', 'license_expiration'];
        $header = array_map('trim', $header);
        $missing = array_diff($expected, $header);
        if (! empty($missing)) {
            fclose($handle);
            return redirect()->to('/admin/omadas/import')
                ->with('error', 'Invalid CSV columns. Download the sample template and use the same headers: ' . implode(', ', $expected));
        }

        $created = 0;
        $errors = [];
        $rowNum = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            $assoc = array_combine($header, array_pad($row, count($header), null));
            if ($assoc === false) {
                continue;
            }
            $assoc = array_map('trim', $assoc);
            if (empty(array_filter($assoc))) {
                continue;
            }

            $data = [];
            foreach ($expected as $key) {
                $data[$key] = $assoc[$key] ?? null;
            }
            if (! empty($data['account_linked_email'])) {
                LinkedAccount::firstOrCreate(
                    ['email' => $data['account_linked_email']],
                    ['email' => $data['account_linked_email']]
                );
            }
            $data['license_expiration'] = ! empty($data['license_expiration']) ? $data['license_expiration'] : null;

            try {
                Omada::create($data);
                $created++;
            } catch (\Throwable $e) {
                $errors[] = "Row {$rowNum}: " . $e->getMessage();
            }
        }
        fclose($handle);

        $message = $created > 0
            ? "Imported {$created} Omada device(s) successfully."
            : 'No devices were imported.';
        if (! empty($errors)) {
            $message .= ' ' . count($errors) . ' row(s) had errors: ' . implode('; ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= '…';
            }
        }

        return redirect()->to('/admin/omadas')
            ->with($created > 0 ? 'success' : 'warning', $message);
    }
}
