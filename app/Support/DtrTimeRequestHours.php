<?php

namespace App\Support;

use App\Models\Dtr;
use App\Models\DtrTimeRequest;

class DtrTimeRequestHours
{
    public const STANDARD_DAY_HOURS = 8.0;

    /**
     * @return array{regular: float, overtime: float}
     */
    public static function splitTotalHours(float $totalHours): array
    {
        $regular = min($totalHours, self::STANDARD_DAY_HOURS);
        $overtime = max($totalHours - self::STANDARD_DAY_HOURS, 0);

        return [
            'regular' => round($regular, 2),
            'overtime' => round($overtime, 2),
        ];
    }

    public static function decimalToTimeString(float $hours): string
    {
        $totalMinutes = (int) round($hours * 60);
        $h = intdiv($totalMinutes, 60);
        $m = $totalMinutes % 60;

        return sprintf('%02d:%02d', $h, $m);
    }

    public static function timeStringToDecimal(string $time): float
    {
        $parts = explode(':', $time);

        return (float) $parts[0] + ((float) ($parts[1] / 60));
    }

    public static function requestTypeLabel(?string $type, bool $forStudent = false): string
    {
        return match ($type) {
            'overtime' => $forStudent ? 'Additional Time' : 'Overtime',
            default => 'Regular',
        };
    }

    /**
     * Apply an approved time request to the student's DTR for that date.
     */
    public static function applyApprovedRequestToDtr(DtrTimeRequest $timeRequest, ?string $adminRemark = null): Dtr
    {
        $existingDtr = Dtr::query()
            ->where('user_id', $timeRequest->user_id)
            ->whereDate('date', $timeRequest->date)
            ->first();

        $hours = (float) $timeRequest->hours;
        $forStudent = $timeRequest->user?->role === 'student';
        $remark = 'Approved '.self::requestTypeLabel($timeRequest->request_type, $forStudent).' time request';
        if ($timeRequest->remarks) {
            $remark .= ': '.$timeRequest->remarks;
        }
        if ($adminRemark) {
            $remark .= ' | Admin: '.$adminRemark;
        }

        if ($timeRequest->request_type === 'overtime') {
            return self::applyOvertimeApproval($existingDtr, $timeRequest, $hours, $remark);
        }

        return self::applyRegularApproval($existingDtr, $timeRequest, $hours, $remark);
    }

    private static function applyRegularApproval(?Dtr $dtr, DtrTimeRequest $timeRequest, float $hours, string $remark): Dtr
    {
        $regularHours = min($hours, self::STANDARD_DAY_HOURS);
        $existingOvertime = $dtr ? (float) ($dtr->overtime_hours ?? 0) : 0;

        if ($dtr) {
            $dtr->total_hours = $regularHours + $existingOvertime;
            $dtr->overtime_hours = $existingOvertime;
            $dtr->status = $dtr->status ?: 'present';
            $dtr->added_time_from_note = 0;
            $dtr->remarks = self::mergeRemarks($dtr->remarks, $remark);
            $dtr->save();

            return $dtr;
        }

        return Dtr::create([
            'user_id' => $timeRequest->user_id,
            'date' => $timeRequest->date,
            'total_hours' => $regularHours,
            'overtime_hours' => 0,
            'status' => 'present',
            'remarks' => $remark,
            'added_time_from_note' => 0,
        ]);
    }

    private static function applyOvertimeApproval(?Dtr $dtr, DtrTimeRequest $timeRequest, float $hours, string $remark): Dtr
    {
        if ($dtr) {
            $newTotal = ((float) ($dtr->total_hours ?? 0)) + $hours;
            $newOvertime = ((float) ($dtr->overtime_hours ?? 0)) + $hours;
            $dtr->total_hours = $newTotal;
            $dtr->overtime_hours = $newOvertime;
            $dtr->status = $dtr->status ?: 'present';
            $dtr->remarks = self::mergeRemarks($dtr->remarks, $remark);
            $dtr->save();

            return $dtr;
        }

        $baseHours = self::STANDARD_DAY_HOURS;
        $totalHours = $baseHours + $hours;

        return Dtr::create([
            'user_id' => $timeRequest->user_id,
            'date' => $timeRequest->date,
            'total_hours' => $totalHours,
            'overtime_hours' => $hours,
            'status' => 'present',
            'remarks' => $remark,
            'added_time_from_note' => 0,
        ]);
    }

    private static function mergeRemarks(?string $existing, string $new): string
    {
        $existing = trim((string) $existing);

        return $existing !== '' ? $existing.' | '.$new : $new;
    }
}
