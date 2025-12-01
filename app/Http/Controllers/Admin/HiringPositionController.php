<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HiringPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HiringPositionController extends Controller
{
    public function index()
    {
        $positions = HiringPosition::with(['creator', 'applications'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => HiringPosition::count(),
            'active' => HiringPosition::where('is_active', true)->count(),
            'inactive' => HiringPosition::where('is_active', false)->count(),
            'total_applications' => HiringPosition::sum('application_count'),
        ];

        return view('admin.hiring-positions.index', compact('positions', 'stats'));
    }

    public function create()
    {
        return view('admin.hiring-positions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:hiring_positions,slug',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'responsibilities' => 'nullable|string',
            'department' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'employment_type' => 'nullable|string|max:255',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
            'is_active' => 'boolean',
            'application_deadline' => 'nullable|date|after:today',
        ]);

        $position = HiringPosition::create([
            'title' => $request->title,
            'slug' => $request->slug ?: (new HiringPosition(['title' => $request->title]))->generateSlug(),
            'description' => $request->description,
            'requirements' => $request->requirements,
            'responsibilities' => $request->responsibilities,
            'department' => $request->department,
            'location' => $request->location,
            'employment_type' => $request->employment_type,
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'is_active' => $request->has('is_active'),
            'application_deadline' => $request->application_deadline,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.hiring-positions.index')
            ->with('success', 'Hiring position created successfully.');
    }

    public function show(HiringPosition $hiringPosition)
    {
        $hiringPosition->load(['creator', 'applications.reviewer']);
        return view('admin.hiring-positions.show', compact('hiringPosition'));
    }

    public function edit(HiringPosition $hiringPosition)
    {
        return view('admin.hiring-positions.edit', compact('hiringPosition'));
    }

    public function update(Request $request, HiringPosition $hiringPosition)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:hiring_positions,slug,' . $hiringPosition->id,
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'responsibilities' => 'nullable|string',
            'department' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'employment_type' => 'nullable|string|max:255',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
            'is_active' => 'boolean',
            'application_deadline' => 'nullable|date',
        ]);

        $hiringPosition->update([
            'title' => $request->title,
            'slug' => $request->slug ?: $hiringPosition->generateSlug(),
            'description' => $request->description,
            'requirements' => $request->requirements,
            'responsibilities' => $request->responsibilities,
            'department' => $request->department,
            'location' => $request->location,
            'employment_type' => $request->employment_type,
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'is_active' => $request->has('is_active'),
            'application_deadline' => $request->application_deadline,
        ]);

        return redirect()->route('admin.hiring-positions.index')
            ->with('success', 'Hiring position updated successfully.');
    }

    public function destroy(HiringPosition $hiringPosition)
    {
        // Check if position has applications
        if ($hiringPosition->applications()->count() > 0) {
            return redirect()->route('admin.hiring-positions.index')
                ->with('error', 'Cannot delete position with existing applications. Please delete or reassign applications first.');
        }

        $hiringPosition->delete();

        return redirect()->route('admin.hiring-positions.index')
            ->with('success', 'Hiring position deleted successfully.');
    }

    public function toggleStatus(HiringPosition $hiringPosition)
    {
        $hiringPosition->update([
            'is_active' => !$hiringPosition->is_active,
        ]);

        return redirect()->back()
            ->with('success', 'Position status updated successfully.');
    }
}
