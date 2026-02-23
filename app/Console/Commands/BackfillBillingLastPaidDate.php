<?php

namespace App\Console\Commands;

use App\Models\BillingStatement;
use App\Models\Starlink;
use Carbon\Carbon;
use Illuminate\Console\Command;

class BackfillBillingLastPaidDate extends Command
{
    protected $signature = 'billing:backfill-last-paid';

    protected $description = 'Backfill last_paid_date for starlinks from billing statements (fix for devices marked as paid before last_paid_date was in fillable)';

    public function handle(): int
    {
        $statements = BillingStatement::orderBy('created_at')->get();
        $updated = 0;

        foreach ($statements as $statement) {
            $ids = $statement->starlink_ids ?? [];
            if (empty($ids)) {
                continue;
            }

            $starlinks = Starlink::whereIn('id', $ids)->get();
            $createdAt = $statement->created_at->copy()->startOfDay();

            foreach ($starlinks as $starlink) {
                $paidDate = null;

                if ($statement->type === 'ongoing') {
                    $paidDate = $this->getOngoingPaidDate($starlink, $createdAt);
                } elseif ($statement->type === 'overdue') {
                    $paidDate = $this->getOverduePaidDate($starlink, $createdAt);
                }
                // 'advance' updates advance_payment_until, not last_paid_date

                if ($paidDate) {
                    $current = $starlink->last_paid_date;
                    if (! $current || $paidDate->gt($current)) {
                        $starlink->last_paid_date = $paidDate;
                        $starlink->save();
                        $updated++;
                        $this->line("  Starlink {$starlink->id} ({$starlink->starlink_id}): last_paid_date = {$paidDate->format('Y-m-d')}");
                    }
                }
            }
        }

        $this->info("Backfilled last_paid_date for {$updated} starlink(s).");

        return 0;
    }

    private function getOngoingPaidDate(Starlink $starlink, Carbon $createdAt): ?Carbon
    {
        if (! $starlink->start_date) {
            return null;
        }
        $nextMonthStart = $createdAt->copy()->addMonth()->startOfMonth();
        $nextMonthEnd = $nextMonthStart->copy()->endOfMonth();
        $billingDay = min($starlink->start_date->day, $nextMonthEnd->day);
        $paidDate = $nextMonthStart->copy()->day($billingDay);

        return $paidDate->between($nextMonthStart, $nextMonthEnd) ? $paidDate : null;
    }

    private function getOverduePaidDate(Starlink $starlink, Carbon $createdAt): ?Carbon
    {
        if (! $starlink->start_date) {
            return null;
        }
        $interval = $starlink->billing_interval ?? 'monthly';
        $billingDay = $starlink->start_date->day;

        if ($starlink->last_paid_date) {
            $next = $interval === 'yearly'
                ? $starlink->last_paid_date->copy()->addYear()
                : $starlink->last_paid_date->copy()->addMonth();
            if ($interval === 'monthly') {
                $day = min($billingDay, $next->copy()->endOfMonth()->day);
                $next->day($day);
            }
            return $next->lt($createdAt) ? $next : null;
        }

        return $starlink->start_date->copy()->startOfDay();
    }
}
