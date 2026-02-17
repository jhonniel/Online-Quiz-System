<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $perPage = (int) $request->input('per_page', 20);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $query = Department::withCount('users')->orderBy('name');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                if (ctype_digit($search)) {
                    $q->orWhere('id', (int) $search);
                }

                $q->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('supervisor_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
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
            'code' => 'nullable|string|max:50|unique:departments,code',
            'description' => 'nullable|string',
            'supervisor_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $department = Department::create([
            'name' => $request->name,
            'code' => $request->code ?: Str::upper(Str::limit(Str::slug($request->name), 10, '')),
            'description' => $request->description,
            'supervisor_name' => $request->supervisor_name,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect('/admin/departments')
            ->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        return view('admin.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'code' => 'nullable|string|max:50|unique:departments,code,' . $department->id,
            'description' => 'nullable|string',
            'supervisor_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $department->update([
            'name' => $request->name,
            'code' => $request->code ?: Str::upper(Str::limit(Str::slug($request->name), 10, '')),
            'description' => $request->description,
            'supervisor_name' => $request->supervisor_name,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect('/admin/departments')
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        // Check if department has users
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
            'is_active' => !$department->is_active,
        ]);

        return redirect()->back()
            ->with('success', 'Department status updated successfully.');
    }
}

