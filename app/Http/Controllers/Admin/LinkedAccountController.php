<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LinkedAccount;
use App\Models\Omada;
use App\Models\Starlink;
use Illuminate\Support\Facades\DB;

class LinkedAccountController extends Controller
{
    private function ensureFullAccess(): void
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403, 'Full admin access required to view Linked Accounts.');
        }
    }

    /**
     * Dashboard of all linked accounts with stats and chart data.
     */
    public function index()
    {
        $this->ensureFullAccess();

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

        $total = LinkedAccount::count();
        $totalStarlinks = Starlink::count();
        $totalOmadas = Omada::count();
        $omadaActiveCount = Omada::whereNotNull('license_expiration')->where('license_expiration', '>=', now()->startOfDay())->count();
        $omadaExpiredCount = Omada::whereNotNull('license_expiration')->where('license_expiration', '<', now()->startOfDay())->count();
        $recentLinkedAccounts = LinkedAccount::withCount('starlinks')->with('user')->latest('created_at')->take(5)->get();

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
            'planTypeData'
        ));
    }
}
