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
     * Regular requests credit up to 08:00 plus any Additional Time filed the same day
     * (requested_total_hours above 08:00), so training remaining includes the full day.
     */
    public static function applyApprovedRequestToDtr(DtrTimeRequest $timeRequest, ?string $adminRemark = null): Dtr
    {
        $timeRequest->loadMissing('user');

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
        $requestedTotal = (float) ($timeRequest->requested_total_hours ?? $hours);
        if ($requestedTotal < $regularHours) {
            $requestedTotal = $regularHours;
        }

        $overtimeFromRequest = max($requestedTotal - self::STANDARD_DAY_HOURS, 0);
        $existingOvertime = $dtr ? (float) ($dtr->overtime_hours ?? 0) : 0;

        // Same calendar day: keep the higher OT amount so we don't wipe leave-credited OT,
        // and don't double-add when both the time request and leave carry the same OT.
        $overtimeHours = max($existingOvertime, $overtimeFromRequest);
        $totalHours = $regularHours + $overtimeHours;

        if ($overtimeFromRequest > 0) {
            $remark .= ' | Additional Time included ('.self::decimalToTimeString($overtimeFromRequest).')';
        }

        if ($dtr) {
            $dtr->total_hours = $totalHours;
            $dtr->overtime_hours = $overtimeHours;
            $dtr->status = $dtr->status ?: 'present';
            $dtr->added_time_from_note = 0;
            $dtr->remarks = self::mergeRemarks($dtr->remarks, $remark);
            $dtr->save();

            return $dtr;
        }

        return Dtr::create([
            'user_id' => $timeRequest->user_id,
            'date' => $timeRequest->date,
            'total_hours' => $totalHours,
            'overtime_hours' => $overtimeHours,
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
