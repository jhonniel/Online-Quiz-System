<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LinkedAccount;
use App\Models\Omada;
use App\Models\Starlink;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LinkedAccountController extends Controller
{
    /**
     * Allow access for users with Linked Accounts permission (route uses admin.permission:linked_accounts).
     */
    private function ensureCanAccess(): void
    {
        if (auth()->user()->canAccessLinkedAccounts()) {
            return;
        }
        abort(403, 'You do not have permission to view Linked Accounts.');
    }

    /**
     * Dashboard of all linked accounts with stats and chart data.
     */
    public function index()
    {
        $this->ensureCanAccess();

        // Backfill linked_accounts from existing Starlink/Omada emails so dashboard shows data
        $emailsFromStarlinks = collect(DB::table('starlinks')->whereNotNull('account_linked_email')->where('account_linked_email', '!=', '')->distinct()->pluck('account_linked_email'))->filter();
        $emailsFromOmadas = collect(DB::table('omadas')->whereNotNull('account_linked_email')->where('account_linked_email', '!=', '')->distinct()->pluck('account_linked_email'))->filter();
        foreach ($emailsFromStarlinks->merge($emailsFromOmadas)->unique() as $email) {
            LinkedAccount::firstOrCreate(['email' => $email], ['email' => $email]);
        }
        // Link Starlinks that have account_linked_email but no linked_account_id
        $accountIdsByEmail = LinkedAccount::whereIn('email', $emailsFromStarlinks)->pluck('id', 'email');
        Starlink::whereNull('linked_account_id')->whereNotNull('account_linked_email')->where('account_linked_email', '!=', '')->get()->each(function ($starlink) use ($accountIdsByEmail) {
            $id = $accountIdsByEmail[$starlink->account_linked_email] ?? null;
            if ($id) {
                $starlink->update(['linked_account_id' => $id]);
            }
        });

        $linkedAccounts = LinkedAccount::withCount('starlinks')
            ->with('user')
            ->orderBy('email')
            ->paginate(20);

        // Add omadas count per account (match by account_linked_email = linked_accounts.email)
        $linkedAccountEmails = $linkedAccounts->pluck('email')->filter()->values()->all();
        $omadaCountsByEmail = [];
        if (!empty($linkedAccountEmails)) {
            $counts = Omada::selectRaw('account_linked_email, count(*) as cnt')
                ->whereIn('account_linked_email', $linkedAccountEmails)
                ->groupBy('account_linked_email')
                ->pluck('cnt', 'account_linked_email');
            $omadaCountsByEmail = $counts->all();
        }
        $linkedAccounts->each(function ($account) use ($omadaCountsByEmail) {
            $account->omadas_count = $omadaCountsByEmail[$account->email] ?? 0;
        });

        // Ongoing and overdue billing counts per account (Starlink + Omada)
        $today = now()->startOfDay();
        $nextMonthStart = $today->copy()->addMonth()->startOfMonth();
        $nextMonthEnd = $nextMonthStart->copy()->endOfMonth();

        $starlinksOngoing = Starlink::with('linkedAccount')
            ->whereNotNull('start_date')
            ->where(function ($q) use ($nextMonthStart) {
                $q->whereNull('advance_payment_until')
                    ->orWhere('advance_payment_until', '<', $nextMonthStart);
            })
            ->get()
            ->filter(function ($s) use ($nextMonthStart, $nextMonthEnd) {
                if (! $s->next_billing_date || ! $s->next_billing_date->between($nextMonthStart, $nextMonthEnd)) {
                    return false;
                }
                return ! $s->last_paid_date || $s->last_paid_date->lt($s->next_billing_date);
            });

        $starlinksOverdue = Starlink::with('linkedAccount')
            ->whereNotNull('start_date')
            ->where(function ($q) use ($today) {
                $q->whereNull('advance_payment_until')
                    ->orWhere('advance_payment_until', '<', $today);
            })
            ->get()
            ->filter(function ($s) use ($today) {
                $billingDate = $this->getCurrentPeriodBillingDate($s);
                return $billingDate && $billingDate->lt($today) && (! $s->last_paid_date || $s->last_paid_date->lt($billingDate));
            });

        $omadaOngoing = Omada::whereNotNull('license_expiration')
            ->where('license_expiration', '>=', $today)
            ->get();
        $omadaOverdue = Omada::whereNotNull('license_expiration')
            ->where('license_expiration', '<', $today)
            ->get();

        $ongoingCountByEmail = [];
        $overdueCountByEmail = [];
        $getAccount = fn ($s) => $s->account_linked_email ?? $s->linkedAccount?->email ?? null;
        foreach ($starlinksOngoing as $s) {
            $email = $getAccount($s);
            if ($email) {
                $ongoingCountByEmail[$email] = ($ongoingCountByEmail[$email] ?? 0) + 1;
            }
        }
        foreach ($starlinksOverdue as $s) {
            $email = $getAccount($s);
            if ($email) {
                $overdueCountByEmail[$email] = ($overdueCountByEmail[$email] ?? 0) + 1;
            }
        }
        foreach ($omadaOngoing as $o) {
            $email = $o->account_linked_email ?? null;
            if ($email) {
                $ongoingCountByEmail[$email] = ($ongoingCountByEmail[$email] ?? 0) + 1;
            }
        }
        foreach ($omadaOverdue as $o) {
            $email = $o->account_linked_email ?? null;
            if ($email) {
                $overdueCountByEmail[$email] = ($overdueCountByEmail[$email] ?? 0) + 1;
            }
        }

        $linkedAccounts->each(function ($account) use ($ongoingCountByEmail, $overdueCountByEmail) {
            $account->ongoing_billing_count = $ongoingCountByEmail[$account->email] ?? 0;
            $account->overdue_billing_count = $overdueCountByEmail[$account->email] ?? 0;
        });

        $total = LinkedAccount::count();
        $totalStarlinks = Starlink::count();
        $totalOmadas = Omada::count();
        $omadaActiveCount = Omada::whereNotNull('license_expiration')->where('license_expiration', '>=', now()->startOfDay())->count();
        $omadaExpiredCount = Omada::whereNotNull('license_expiration')->where('license_expiration', '<', now()->startOfDay())->count();
        $recentLinkedAccounts = LinkedAccount::withCount('starlinks')->with('user')->latest('created_at')->take(5)->get();
        $recentLinkedAccounts->each(function ($account) use ($ongoingCountByEmail, $overdueCountByEmail) {
            $account->ongoing_billing_count = $ongoingCountByEmail[$account->email] ?? 0;
            $account->overdue_billing_count = $overdueCountByEmail[$account->email] ?? 0;
        });

        // Chart: Linked accounts created per week (last 12 weeks) – DB-agnostic
        $twelveWeeksAgo = now()->subWeeks(12)->startOfWeek();
        $weekLabels = [];
        $weekData = [];
        for ($i = 11; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();
            $weekLabels[] = $weekStart->format('M j');
            $count = LinkedAccount::whereBetween('created_at', [$weekStart, $weekEnd])->count();
            $weekData[] = $count;
        }

        // Chart: Device breakdown (Starlink vs Omada)
        $deviceBreakdownLabels = ['Starlink', 'Omada'];
        $deviceBreakdownData = [$totalStarlinks, $totalOmadas];

        // Chart: Omada license status (Active vs Expired)
        $omadaStatusLabels = ['Active', 'Expired'];
        $omadaStatusData = [$omadaActiveCount, $omadaExpiredCount];

        // Chart: Starlink plan types (group by plan)
        $planCountsRaw = Starlink::select('plan')->get()->groupBy(function ($s) {
            $p = $s->plan;
            return ($p !== null && trim((string) $p) !== '') ? trim($p) : 'No plan';
        })->map->count()->sortDesc();
        $planTypeLabels = $planCountsRaw->keys()->values()->all();
        $planTypeData = $planCountsRaw->values()->all();

        // Chart/list: Client name counts (stored in starlinks.municipality)
        $clientNameCountsTop = collect();
        $clientNameUniqueCount = 0;
        if (Schema::hasColumn('starlinks', 'municipality')) {
            $clientNameCountsRaw = Starlink::select('municipality')->get()->groupBy(function ($s) {
                $v = $s->municipality !== null ? trim((string) $s->municipality) : '';
                return $v !== '' ? $v : null;
            })->filter()->map->count()->sortDesc();

            $clientNameUniqueCount = $clientNameCountsRaw->count();
            // Show all client names (not just top 5) so the UI can list every value.
            $clientNameCountsTop = $clientNameCountsRaw;
        }

        // Starlinks to be billed next month: have start_date, respect billing_interval (monthly/yearly) and advance_payment_until
        $nextMonthStart = now()->addMonth()->startOfMonth();
        $nextMonthEnd = $nextMonthStart->copy()->endOfMonth();
        $starlinksToBillNextMonth = Starlink::with('linkedAccount')
            ->whereNotNull('start_date')
            ->where(function ($q) use ($nextMonthStart) {
                $q->whereNull('advance_payment_until')
                    ->orWhere('advance_payment_until', '<', $nextMonthStart);
            })
            ->get()
            ->filter(function ($s) use ($nextMonthStart, $nextMonthEnd) {
                if (! $s->next_billing_date || ! $s->next_billing_date->between($nextMonthStart, $nextMonthEnd)) {
                    return false;
                }
                if ($s->last_paid_date && $s->last_paid_date->gte($s->next_billing_date)) {
                    return false;
                }
                if ($s->last_paid_date && $s->last_paid_date->gte($nextMonthStart)) {
                    return false;
                }
                return true;
            })
            ->sortBy(fn ($s) => $s->next_billing_date?->format('Y-m-d'))
            ->values();

        // Omada devices with active licenses (ongoing billing)
        $omadaOngoingBilling = Omada::whereNotNull('license_expiration')
            ->where('license_expiration', '>=', now()->startOfDay())
            ->orderBy('license_expiration')
            ->get();

        return view('admin.linked-accounts.index', compact(
            'linkedAccounts',
            'total',
            'totalStarlinks',
            'totalOmadas',
            'omadaActiveCount',
            'omadaExpiredCount',
            'recentLinkedAccounts',
            'weekLabels',
            'weekData',
            'deviceBreakdownLabels',
            'deviceBreakdownData',
            'omadaStatusLabels',
            'omadaStatusData',
            'planTypeLabels',
            'planTypeData',
            'clientNameCountsTop',
            'clientNameUniqueCount',
            'starlinksToBillNextMonth',
            'nextMonthStart',
            'omadaOngoingBilling'
        ));
    }

    /**
     * First unpaid billing date (from last_paid_date + 1 period, or start_date if never paid).
     */
    private function getCurrentPeriodBillingDate(Starlink $s): ?Carbon
    {
        if (! $s->start_date) {
            return null;
        }
        $interval = $s->billing_interval ?? 'monthly';
        $billingDay = $s->start_date->day;

        if ($s->last_paid_date) {
            $next = $interval === 'yearly'
                ? $s->last_paid_date->copy()->addYear()
                : $s->last_paid_date->copy()->addMonth();
            if ($interval === 'monthly') {
                $day = min($billingDay, $next->copy()->endOfMonth()->day);
                $next->day($day);
            }
            return $next;
        }

        return $s->start_date->copy()->startOfDay();
    }
}
