<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Dtr;
use Illuminate\Http\Request;

class StudentDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get all active students with their required training hours
        $students = User::with('university')
            ->where('role', 'student')
            ->where('is_active', true)
            ->get();

        if ($students->isEmpty()) {
            $ranked = collect();
        } else {
            $studentIds = $students->pluck('id');

            // Sum total DTR hours per student (using total_hours field)
            $totalsByStudent = Dtr::whereIn('user_id', $studentIds)
                ->selectRaw('user_id, COALESCE(SUM(total_hours), 0) as total_hours_sum')
                ->groupBy('user_id')
                ->pluck('total_hours_sum', 'user_id');

            $ranked = $students->map(function ($student) use ($totalsByStudent) {
                $required = (float) ($student->required_training_hours ?? 0);
                $total = (float) ($totalsByStudent[$student->id] ?? 0);
                $remaining = $required - $total; // can be negative

                return [
                    'student' => $student,
                    'required_hours' => $required,
                    'total_hours' => $total,
                    'remaining_hours' => $remaining,
                    'required_hours_formatted' => $this->formatHours($required),
                    'total_hours_formatted' => $this->formatHours($total),
                    'remaining_hours_formatted' => $this->formatHours($remaining),
                ];
            })->sortByDesc('remaining_hours')->values();
        }

        return view('admin.student-management.dashboard', [
            'students' => $ranked,
        ]);
    }

    protected function formatHours(float $hours): string
    {
        $isNegative = $hours < 0;
        $minutes = (int) round(abs($hours) * 60);
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;

        return ($isNegative ? '-' : '') . sprintf('%02d:%02d', $h, $m);
    }
}


