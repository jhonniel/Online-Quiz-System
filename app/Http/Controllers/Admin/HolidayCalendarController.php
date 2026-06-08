<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DtrHoliday;
use App\Support\DtrHolidayCalendar;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HolidayCalendarController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now('Asia/Manila');
        $monthParam = $request->input('month', $now->format('Y-m'));

        try {
            $currentMonth = Carbon::createFromFormat('Y-m', $monthParam, 'Asia/Manila')->startOfMonth();
        } catch (\Exception $e) {
            $currentMonth = $now->copy()->startOfMonth();
        }

        $editHoliday = null;
        if ($request->filled('edit')) {
            $editHoliday = DtrHoliday::find($request->input('edit'));
        } elseif (old('_method') === 'PUT' && old('holiday_id')) {
            $editHoliday = DtrHoliday::find(old('holiday_id'));
        }

        return view('admin.system.holiday-calendar', [
            'currentMonth' => $currentMonth,
            'weeks' => DtrHolidayCalendar::buildMonthWeeks($currentMonth),
            'monthHolidays' => DtrHolidayCalendar::forMonth($currentMonth),
            'prevMonth' => $currentMonth->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $currentMonth->copy()->addMonth()->format('Y-m'),
            'editHoliday' => $editHoliday,
            'prefillDate' => $request->input('date'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date|unique:dtr_holidays,date',
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:regular,special',
            'notes' => 'nullable|string|max:2000',
        ]);

        DtrHoliday::create([
            'date' => $data['date'],
            'name' => $data['name'],
            'type' => $data['type'],
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $month = Carbon::parse($data['date'])->format('Y-m');

        return redirect()
            ->route('admin.system.calendar.index', ['month' => $month])
            ->with('success', 'Holiday saved. DTR will auto-credit 8 hours on this date.');
    }

    public function update(Request $request, DtrHoliday $dtrHoliday)
    {
        $data = $request->validate([
            'date' => 'required|date|unique:dtr_holidays,date,'.$dtrHoliday->id,
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:regular,special',
            'notes' => 'nullable|string|max:2000',
        ]);

        $dtrHoliday->update([
            'date' => $data['date'],
            'name' => $data['name'],
            'type' => $data['type'],
            'notes' => $data['notes'] ?? null,
        ]);

        $month = Carbon::parse($data['date'])->format('Y-m');

        return redirect()
            ->route('admin.system.calendar.index', ['month' => $month])
            ->with('success', 'Holiday updated.');
    }

    public function destroy(DtrHoliday $dtrHoliday)
    {
        $month = $dtrHoliday->date->format('Y-m');
        $dtrHoliday->delete();

        return redirect()
            ->route('admin.system.calendar.index', ['month' => $month])
            ->with('success', 'Holiday removed.');
    }
}
