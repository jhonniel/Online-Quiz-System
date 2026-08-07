<?php

namespace App\Console\Commands;

use App\Models\Dtr;
use App\Models\DtrTimeRequest;
use App\Support\DtrTimeRequestHours;
use Illuminate\Console\Command;

class BackfillStudentAdditionalTimeToDtrCommand extends Command
{
    protected $signature = 'dtr:backfill-student-additional-time
                            {--dry-run : Show what would change without writing}
                            {--user= : Limit to a single user id}';

    protected $description = 'Backfill Additional Time (hours above 08:00) from approved student time requests onto DTR so remaining training hours include OT';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $userId = $this->option('user');

        $query = DtrTimeRequest::query()
            ->with('user')
            ->where('status', 'approved')
            ->where(function ($q): void {
                $q->where('request_type', 'regular')
                    ->orWhereNull('request_type');
            })
            ->whereNotNull('requested_total_hours')
            ->where('requested_total_hours', '>', DtrTimeRequestHours::STANDARD_DAY_HOURS)
            ->whereHas('user', fn ($q) => $q->where('role', 'student'));

        if ($userId !== null && $userId !== '') {
            $query->where('user_id', (int) $userId);
        }

        $updated = 0;
        $skipped = 0;

        $query->orderBy('id')->chunkById(100, function ($requests) use ($dryRun, &$updated, &$skipped): void {
            foreach ($requests as $request) {
                $requestedTotal = (float) $request->requested_total_hours;
                $overtimeNeeded = max($requestedTotal - DtrTimeRequestHours::STANDARD_DAY_HOURS, 0);
                if ($overtimeNeeded <= 0) {
                    $skipped++;
                    continue;
                }

                $dateStr = $request->date?->format('Y-m-d');
                if (! $dateStr) {
                    $skipped++;
                    continue;
                }

                $dtr = Dtr::query()
                    ->where('user_id', $request->user_id)
                    ->whereDate('date', $dateStr)
                    ->first();

                $regularHours = min((float) $request->hours, DtrTimeRequestHours::STANDARD_DAY_HOURS);
                $currentOvertime = $dtr ? (float) ($dtr->overtime_hours ?? 0) : 0;

                if ($currentOvertime + 0.009 >= $overtimeNeeded) {
                    $skipped++;
                    continue;
                }

                $newOvertime = $overtimeNeeded;
                $newTotal = $regularHours + $newOvertime;
                $label = DtrTimeRequestHours::decimalToTimeString($overtimeNeeded);

                $this->line(sprintf(
                    'User %d %s: DTR OT %s → %s (total %s)',
                    $request->user_id,
                    $dateStr,
                    DtrTimeRequestHours::decimalToTimeString($currentOvertime),
                    $label,
                    DtrTimeRequestHours::decimalToTimeString($newTotal)
                ));

                if ($dryRun) {
                    $updated++;
                    continue;
                }

                $remark = 'Backfilled Additional Time ('.$label.') from approved time request';

                if ($dtr) {
                    $dtr->total_hours = $newTotal;
                    $dtr->overtime_hours = $newOvertime;
                    $dtr->status = $dtr->status ?: 'present';
                    $existingRemarks = trim((string) ($dtr->remarks ?? ''));
                    if (! str_contains($existingRemarks, $remark)) {
                        $dtr->remarks = $existingRemarks !== ''
                            ? $existingRemarks.' | '.$remark
                            : $remark;
                    }
                    $dtr->save();
                } else {
                    Dtr::create([
                        'user_id' => $request->user_id,
                        'date' => $dateStr,
                        'total_hours' => $newTotal,
                        'overtime_hours' => $newOvertime,
                        'status' => 'present',
                        'remarks' => $remark,
                        'added_time_from_note' => 0,
                    ]);
                }

                $updated++;
            }
        });

        $this->info(($dryRun ? '[dry-run] ' : '')."Updated {$updated} DTR row(s); skipped {$skipped}.");

        return self::SUCCESS;
    }
}
