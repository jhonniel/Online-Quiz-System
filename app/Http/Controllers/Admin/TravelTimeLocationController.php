<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\TimeExtraction;
use App\Models\TravelTimeLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TravelTimeLocationController extends Controller
{
    public function index(Request $request): View
    {
        $locations = TravelTimeLocation::ordered()->get();

        $editLocation = null;
        if ($request->filled('edit')) {
            $editLocation = TravelTimeLocation::find($request->input('edit'));
        } elseif (old('_method') === 'PUT' && old('location_id')) {
            $editLocation = TravelTimeLocation::find(old('location_id'));
        }

        return view('admin.system.travel-time.index', compact('locations', 'editLocation'));
    }

    public function edit(TravelTimeLocation $travelTimeLocation): RedirectResponse
    {
        return redirect()->route('admin.system.travel-time.index', ['edit' => $travelTimeLocation->id]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->normalizeValidatedHours($this->validateLocation($request));

        $maxOrder = (int) TravelTimeLocation::max('sort_order');

        TravelTimeLocation::create([
            'name' => $validated['name'],
            'hours' => $validated['hours'],
            'trip_type' => $validated['trip_type'],
            'sort_order' => $validated['sort_order'] ?? ($maxOrder + 1),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.system.travel-time.index')
            ->with('success', 'Travel time location added.');
    }

    public function update(Request $request, TravelTimeLocation $travelTimeLocation): RedirectResponse
    {
        $validated = $this->normalizeValidatedHours($this->validateLocation($request, $travelTimeLocation));

        $travelTimeLocation->update([
            'name' => $validated['name'],
            'hours' => $validated['hours'],
            'trip_type' => $validated['trip_type'],
            'sort_order' => $validated['sort_order'] ?? $travelTimeLocation->sort_order,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.system.travel-time.index')
            ->with('success', 'Travel time location updated.');
    }

    public function destroy(TravelTimeLocation $travelTimeLocation): RedirectResponse
    {
        $travelTimeLocation->delete();

        return redirect()
            ->route('admin.system.travel-time.index')
            ->with('success', 'Travel time location removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateLocation(Request $request, ?TravelTimeLocation $existing = null): array
    {
        $uniqueName = Rule::unique('travel_time_locations', 'name');
        if ($existing !== null) {
            $uniqueName = $uniqueName->ignore($existing->id);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255', $uniqueName],
            'hours' => ['required', 'string', 'regex:/^\d{1,3}:\d{2}$/'],
            'trip_type' => ['required', Rule::in(array_keys(TravelTimeLocation::tripTypes()))],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'hours.regex' => 'Please enter travel time in HH:MM format (e.g., 08:00).',
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function normalizeValidatedHours(array $validated): array
    {
        $decimalHours = TimeExtraction::parseHhMmToDecimal((string) $validated['hours']);
        if ($decimalHours === null) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'hours' => 'Please enter a valid travel time in HH:MM format (e.g., 08:00).',
            ]);
        }

        $validated['hours'] = $decimalHours;

        return $validated;
    }
}
