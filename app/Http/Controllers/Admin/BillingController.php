<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillingStatement;
use App\Models\Omada;
use App\Models\Starlink;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    /**
     * Billing page: ongoing billing and overdue devices (Starlink + Omada).
     * Access controlled by admin.permission:billing middleware.
     */
    public function index()
    {

        $today = now()->startOfDay();
        $nextMonthStart = $today->copy()->addMonth()->startOfMonth();
        $nextMonthEnd = $nextMonthStart->copy()->endOfMonth();

        // Starlinks to be billed next month (ongoing) — only show when next billing falls in next month
        // Exclude if already marked paid for that period, or if advance payment covers next month
        $starlinksOngoing = Starlink::with('linkedAccount')
            ->whereNotNull('start_date')
            ->where(function ($q) use ($nextMonthStart) {
                $q->whereNull('advance_payment_until')
                    ->orWhere('advance_payment_until', '<', $nextMonthStart);
            })
            ->get()
            ->filter(function ($s) use ($nextMonthStart, $nextMonthEnd) {
                // Must have next billing date in next month
                if (! $s->next_billing_date || ! $s->next_billing_date->between($nextMonthStart, $nextMonthEnd)) {
                    return false;
                }
                // Exclude if already paid for next billing period (mark as paid)
                if ($s->last_paid_date && $s->last_paid_date->gte($s->next_billing_date)) {
                    return false;
                }
                // Exclude if last_paid_date covers next month (paid for period in next month)
                if ($s->last_paid_date && $s->last_paid_date->gte($nextMonthStart)) {
                    return false;
                }
                return true;
            })
            ->sortBy(fn ($s) => $s->next_billing_date?->format('Y-m-d'))
            ->values();

        // Starlinks overdue: based on last_paid_date — next period has passed, not yet marked paid
        $starlinksOverdue = Starlink::with(['linkedAccount', 'subscriptionPlanType'])
            ->whereNotNull('start_date')
            ->whereNotNull('last_paid_date')
            ->where(function ($q) use ($today) {
                $q->whereNull('advance_payment_until')
                    ->orWhere('advance_payment_until', '<', $today);
            })
            ->get()
            ->filter(function ($s) use ($today) {
                $billingDate = $this->getCurrentPeriodBillingDate($s);
                if (! $billingDate || ! $billingDate->lt($today)) {
                    return false;
                }
                return $s->last_paid_date->lt($billingDate);
            })
            ->map(function ($s) {
                $s->overdue_date = $this->getCurrentPeriodBillingDate($s);
                $s->late_payment_count = $this->getLatePaymentCount($s);

                return $s;
            })
            ->sortBy(fn ($s) => $s->overdue_date?->format('Y-m-d'))
            ->values();

        // Omada ongoing (active licenses)
        $omadaOngoing = Omada::whereNotNull('license_expiration')
            ->where('license_expiration', '>=', $today)
            ->orderBy('license_expiration')
            ->get();

        // Omada overdue (expired licenses)
        $omadaOverdue = Omada::whereNotNull('license_expiration')
            ->where('license_expiration', '<', $today)
            ->orderByDesc('license_expiration')
            ->get();

        // Billing statements (mark as paid / advance payment records)
        $billingStatements = BillingStatement::with('markedByUser')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        // Group by account (account_linked_email or linkedAccount email)
        // Omada expired licenses are not shown on billing page
        $groupedOngoing = $this->groupByAccount($starlinksOngoing, $omadaOngoing);
        $groupedOverdue = $this->groupByAccount($starlinksOverdue, collect());
        $accountEmails = collect(array_keys($groupedOngoing))
            ->merge(array_keys($groupedOverdue))
            ->unique()
            ->sort()
            ->values()
            ->all();

        return view('admin.billing.index', compact(
            'groupedOngoing',
            'groupedOverdue',
            'billingStatements',
            'accountEmails',
            'starlinksOngoing',
            'starlinksOverdue',
            'omadaOngoing',
            'omadaOverdue'
        ));
    }

    /**
     * Bulk mark selected Starlinks as paid for their billing period.
     */
    public function markAsPaid(Request $request)
    {
        $this->ensureFullAccess();

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:starlinks,id',
            'type' => 'required|string|in:ongoing,overdue',
        ]);

        $today = now()->startOfDay();
        $nextMonthStart = $today->copy()->addMonth()->startOfMonth();
        $nextMonthEnd = $nextMonthStart->copy()->endOfMonth();

        $starlinks = Starlink::whereIn('id', $validated['ids'])->get();
        $marked = 0;
        $markedIds = [];

        foreach ($starlinks as $starlink) {
            $paidDate = null;
            if ($validated['type'] === 'ongoing') {
                $nextBilling = $starlink->next_billing_date;
                if ($nextBilling && $nextBilling->between($nextMonthStart, $nextMonthEnd)) {
                    $paidDate = $nextBilling;
                }
            } else {
                $paidDate = $this->getCurrentPeriodBillingDate($starlink);
                if ($paidDate && $paidDate->lt($today)) {
                    // keep paidDate
                } else {
                    $paidDate = null;
                }
            }
            if ($paidDate) {
                $starlink->update(['last_paid_date' => $paidDate]);
                $marked++;
                $markedIds[] = $starlink->id;
            }
        }

        if ($marked > 0) {
            $statement = BillingStatement::create([
                'starlink_ids' => $markedIds,
                'type' => $validated['type'],
                'marked_by' => auth()->id(),
            ]);

            return view('admin.billing.mark-paid-redirect', [
                'statementUrl' => url('/admin/billing/statement/' . $statement->id),
                'billingUrl' => url('/admin/billing'),
                'successMessage' => "{$marked} device(s) marked as paid. Billing statement opened in new tab.",
            ]);
        }

        return redirect()->to('/admin/billing')
            ->with('success', 'No devices were marked as paid.');
    }

    /**
     * Advance payment: set advance_payment_until and generate billing receipt.
     */
    public function markAdvancePayment(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:starlinks,id',
            'type' => 'nullable|string|in:ongoing,overdue',
        ]);

        $starlinks = Starlink::whereIn('id', $validated['ids'])->get();
        $marked = 0;
        $markedIds = [];
        $advanceUntil = null;
        $today = now()->startOfDay();

        foreach ($starlinks as $starlink) {
            $nextBilling = $starlink->next_billing_date;
            $updates = ['advance_payment_until' => $nextBilling];

            if (($validated['type'] ?? null) === 'overdue') {
                $firstUnpaid = $this->getCurrentPeriodBillingDate($starlink);
                if ($firstUnpaid && $firstUnpaid->lt($today)) {
                    $updates['last_paid_date'] = $firstUnpaid;
                }
            }

            if ($nextBilling) {
                $starlink->update($updates);
                $marked++;
                $markedIds[] = $starlink->id;
                if (! $advanceUntil || $nextBilling->gt($advanceUntil)) {
                    $advanceUntil = $nextBilling;
                }
            }
        }

        if ($marked > 0) {
            $statement = BillingStatement::create([
                'starlink_ids' => $markedIds,
                'type' => 'advance',
                'marked_by' => auth()->id(),
                'advance_payment_until' => $advanceUntil,
            ]);

            return view('admin.billing.mark-paid-redirect', [
                'statementUrl' => url('/admin/billing/statement/' . $statement->id),
                'billingUrl' => url('/admin/billing'),
                'successMessage' => "{$marked} device(s) advance payment recorded. Billing receipt opened in new tab.",
            ]);
        }

        return redirect()->to('/admin/billing')
            ->with('success', 'No devices were advanced.');
    }

    /**
     * Show billing statement (paid devices, marked by, download PDF).
     */
    public function showStatement(BillingStatement $billingStatement)
    {
        $billingStatement->load('markedByUser');
        $starlinks = $billingStatement->starlinks;

        return view('admin.billing.statement', compact('billingStatement', 'starlinks'));
    }

    /**
     * Download billing statement as PDF.
     */
    public function downloadPdf(BillingStatement $billingStatement)
    {
        $billingStatement->load('markedByUser');
        $starlinks = $billingStatement->starlinks;

        $pdf = Pdf::loadView('admin.billing.statement-pdf', compact('billingStatement', 'starlinks'))
            ->setPaper('a4', 'portrait');
        $filename = 'billing_statement_' . $billingStatement->id . '_' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
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

    /**
     * Count late payments from last payment date until today (unpaid billing periods).
     */
    private function getLatePaymentCount(Starlink $s): int
    {
        $today = now()->startOfDay();
        if (! $s->start_date || ! $s->last_paid_date) {
            return 0;
        }
        if ($s->advance_payment_until && $s->advance_payment_until->gte($today)) {
            return 0;
        }

        $interval = $s->billing_interval ?? 'monthly';
        $billingDay = $s->start_date->day;
        $check = $s->last_paid_date->copy()->startOfDay();

        if ($interval === 'monthly') {
            $check->addMonth();
            $day = min($billingDay, $check->copy()->endOfMonth()->day);
            $check->day($day);
        } else {
            $check->addYear();
        }

        $count = 0;
        if ($interval === 'monthly') {
            while ($check->lte($today)) {
                $count++;
                $check->addMonth();
                $day = min($billingDay, $check->copy()->endOfMonth()->day);
                $check->day($day);
            }
        } else {
            while ($check->lte($today)) {
                $count++;
                $check->addYear();
            }
        }

        return $count;
    }

    /**
     * Group Starlinks and Omadas by account (email).
     *
     * @return array<string, array{starlinks: \Illuminate\Support\Collection, omadas: \Illuminate\Support\Collection}>
     */
    private function groupByAccount($starlinks, $omadas): array
    {
        $groups = [];
        $getAccount = fn ($s) => $s->account_linked_email ?? $s->linkedAccount?->email ?? '— No account —';
        foreach ($starlinks as $s) {
            $key = $getAccount($s);
            if (! isset($groups[$key])) {
                $groups[$key] = ['starlinks' => collect(), 'omadas' => collect()];
            }
            $groups[$key]['starlinks']->push($s);
        }
        foreach ($omadas as $o) {
            $key = $o->account_linked_email ?? '— No account —';
            if (! isset($groups[$key])) {
                $groups[$key] = ['starlinks' => collect(), 'omadas' => collect()];
            }
            $groups[$key]['omadas']->push($o);
        }
        ksort($groups);

        return $groups;
    }

    /**
     * Group Starlinks by account (email).
     *
     * @return array<string, \Illuminate\Support\Collection>
     */
    private function groupStarlinksByAccount($starlinks): array
    {
        $groups = [];
        $getAccount = fn ($s) => $s->account_linked_email ?? $s->linkedAccount?->email ?? '— No account —';
        foreach ($starlinks as $s) {
            $key = $getAccount($s);
            if (! isset($groups[$key])) {
                $groups[$key] = collect();
            }
            $groups[$key]->push($s);
        }
        ksort($groups);

        return $groups;
    }
}
