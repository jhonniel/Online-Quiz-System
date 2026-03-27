<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\LeaveRequest;

class LeaveBalance extends Model
{
    protected $fillable = [
        'user_id',
        'year',
        'vacation_allowance',
        'sick_allowance',
    ];

    /**
     * The user this balance belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create (or fetch) a LeaveBalance for the given year, carrying over last year's remaining credits.
     *
     * Carryover rule (employee Leave Credits pool):
     * - Remaining credits from prior year (vacation + sick allowance - approved vacation/sick used)
     *   are added to the new year's default combined allowance.
     *
     * We store the combined allowance in vacation_allowance and set sick_allowance to 0
     * to keep a single pooled "Leave Credits" number while retaining legacy columns.
     */
    public static function firstOrCreateWithCarryover(
        int $userId,
        int $year,
        float $defaultVacation,
        float $defaultSick
    ): self {
        $existing = self::where('user_id', $userId)->where('year', $year)->first();
        if ($existing) {
            return $existing;
        }

        $priorYear = $year - 1;
        $carry = 0.0;

        $prior = self::where('user_id', $userId)->where('year', $priorYear)->first();
        if ($prior) {
            $priorAllowance = (float) $prior->vacation_allowance + (float) $prior->sick_allowance;
            $priorUsed = LeaveRequest::where('user_id', $userId)
                ->whereIn('type', ['vacation_leave', 'sick_leave'])
                ->where('status', 'approved')
                ->whereYear('start_date', $priorYear)
                ->get()
                ->sum->days;

            $carry = max($priorAllowance - (float) $priorUsed, 0.0);
        }

        $combinedDefault = $defaultVacation + $defaultSick;
        $newAllowance = $combinedDefault + $carry;

        return self::create([
            'user_id' => $userId,
            'year' => $year,
            'vacation_allowance' => $newAllowance,
            'sick_allowance' => 0,
        ]);
    }
}
