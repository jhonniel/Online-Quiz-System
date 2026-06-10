<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\DepartmentPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $perPage = (int) $request->input('per_page', 20);
        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $query = Department::with(['positions' => fn ($q) => $q->active()->orderBy('sort_order')->orderBy('name')])
            ->withCount('users')
            ->orderBy('name');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                if (ctype_digit($search)) {
                    $q->orWhere('id', (int) $search);
                }

                $q->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('supervisor_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('job_description', 'like', "%{$search}%")
                    ->orWhereHas('positions', fn ($positionQuery) => $positionQuery->where('name', 'like', "%{$search}%"));
            });
        }

        $departments = $query->paginate($perPage)->appends($request->query());

        return view('admin.departments.index', compact('departments', 'search', 'perPage'));
    }

    public function create()
    {
        return view('admin.departments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'positions' => 'nullable|array|max:50',
            'positions.*' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:50|unique:departments,code',
            'description' => 'nullable|string',
            'job_description' => 'nullable|string|max:10000',
            'supervisor_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $department = Department::create([
            'name' => $request->name,
            'code' => $request->code ?: Str::upper(Str::limit(Str::slug($request->name), 10, '')),
            'description' => $request->description,
            'job_description' => $this->normalizeJobDescription($request->input('job_description')),
            'supervisor_name' => $request->supervisor_name,
            'is_active' => $request->has('is_active'),
        ]);

        $this->syncPositions($department, $request->input('positions', []));

        return redirect('/admin/departments')
            ->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        $department->load(['positions' => fn ($q) => $q->orderBy('sort_order')->orderBy('name')]);

        return view('admin.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,'.$department->id,
            'positions' => 'nullable|array|max:50',
            'positions.*' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:50|unique:departments,code,'.$department->id,
            'description' => 'nullable|string',
            'job_description' => 'nullable|string|max:10000',
            'supervisor_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $department->update([
            'name' => $request->name,
            'code' => $request->code ?: Str::upper(Str::limit(Str::slug($request->name), 10, '')),
            'description' => $request->description,
            'job_description' => $this->normalizeJobDescription($request->input('job_description')),
            'supervisor_name' => $request->supervisor_name,
            'is_active' => $request->has('is_active'),
        ]);

        $this->syncPositions($department, $request->input('positions', []));

        return redirect('/admin/departments')
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        if ($department->users()->count() > 0) {
            return redirect('/admin/departments')
                ->with('error', 'Cannot delete department with assigned employees. Please reassign employees first.');
        }

        $department->delete();

        return redirect('/admin/departments')
            ->with('success', 'Department deleted successfully.');
    }

    public function toggleStatus(Department $department)
    {
        $department->update([
            'is_active' => ! $department->is_active,
        ]);

        return redirect()->back()
            ->with('success', 'Department status updated successfully.');
    }

    /**
     * @param  list<mixed>|null  $positions
     */
    private function syncPositions(Department $department, ?array $positions): void
    {
        $names = collect($positions ?? [])
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique()
            ->values();

        $keptIds = [];

        foreach ($names as $index => $name) {
            $position = $department->positions()->firstOrNew(['name' => $name]);
            $position->fill([
                'sort_order' => $index,
                'is_active' => true,
            ]);
            $position->save();
            $keptIds[] = $position->id;
        }

        $department->positions()
            ->whereNotIn('id', $keptIds)
            ->get()
            ->each(function (DepartmentPosition $position): void {
                if ($position->users()->exists()) {
                    $position->update(['is_active' => false]);
                } else {
                    $position->delete();
                }
            });
    }

    private function normalizeJobDescription(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $lines = preg_split('/\R/u', trim($value)) ?: [];
        $normalized = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $line = preg_replace('/^[\-*•]\s*/u', '', $line) ?? $line;
            $line = trim($line);

            if ($line !== '') {
                $normalized[] = $line;
            }
        }

        return $normalized === [] ? null : implode("\n", $normalized);
    }
}
