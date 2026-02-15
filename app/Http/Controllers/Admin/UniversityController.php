<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\Request;

class UniversityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $query = University::withCount('users')->orderBy('name');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                if (ctype_digit($search)) {
                    $q->orWhere('id', (int) $search);
                }

                $q->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $universities = $query->paginate($perPage)->appends($request->query());

        return view('admin.universities.index', compact('universities', 'search', 'perPage'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.universities.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:universities,name',
            'code' => 'nullable|string|max:50|unique:universities,code',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        University::create([
            'name' => $request->name,
            'code' => $request->code,
            'location' => $request->location,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect('/admin/universities')
            ->with('success', 'University created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(University $university)
    {
        $university->load('users');
        return view('admin.universities.show', compact('university'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(University $university)
    {
        return view('admin.universities.edit', compact('university'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, University $university)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:universities,name,' . $university->id,
            'code' => 'nullable|string|max:50|unique:universities,code,' . $university->id,
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $university->update([
            'name' => $request->name,
            'code' => $request->code,
            'location' => $request->location,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect('/admin/universities')
            ->with('success', 'University updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(University $university)
    {
        // Check if university has users
        if ($university->users()->count() > 0) {
            return redirect('/admin/universities')
                ->with('error', 'Cannot delete university. It has ' . $university->users()->count() . ' user(s) associated with it.');
        }

        $university->delete();

        return redirect('/admin/universities')
            ->with('success', 'University deleted successfully.');
    }

    /**
     * Toggle the active status of a university.
     */
    public function toggleStatus(University $university)
    {
        $university->update(['is_active' => !$university->is_active]);

        $status = $university->is_active ? 'activated' : 'deactivated';
        return redirect('/admin/universities')
            ->with('success', "University {$status} successfully.");
    }
}
