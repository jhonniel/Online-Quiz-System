<?php

namespace App\Support;

use App\Mail\StarlinkBillingReminderMail;
use App\Models\Setting;
use App\Models\Starlink;
use App\Services\MailConfigService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

final class StarlinkBillingNotifier
{
    public const EMAILS_SETTING_KEY = 'starlink_billing_notification_emails';

    public const DAYS_BEFORE_SETTING_KEY = 'starlink_billing_notify_days_before';

    public const DEFAULT_NOTIFY_DAYS_BEFORE = 7;

    /**
     * @return list<string>
     */
    public static function notificationEmails(): array
    {
        $raw = (string) Setting::get(self::EMAILS_SETTING_KEY, '');
        if (trim($raw) === '') {
            return [];
        }

        $emails = preg_split('/[\s,;]+/', $raw) ?: [];

        return collect($emails)
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->filter(fn ($email) => $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $emails
     */
    public static function saveNotificationEmails(array $emails): void
    {
        $normalized = collect($emails)
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->filter(fn ($email) => $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->implode(',');

        Setting::set(
            self::EMAILS_SETTING_KEY,
            $normalized,
            'text',
            'Email addresses notified when Starlink devices have incoming or overdue billing dues'
        );
    }

    public static function notifyDaysBefore(): int
    {
        $raw = Setting::get(self::DAYS_BEFORE_SETTING_KEY, self::DEFAULT_NOTIFY_DAYS_BEFORE);
        $days = is_numeric($raw) ? (int) $raw : self::DEFAULT_NOTIFY_DAYS_BEFORE;

        return max(0, min(90, $days));
    }

    public static function saveNotifyDaysBefore(int $days): void
    {
        Setting::set(
            self::DAYS_BEFORE_SETTING_KEY,
            max(0, min(90, $days)),
            'number',
            'Days before next Starlink billing date to send reminder emails'
        );
    }

    /**
     * Unpaid billing cycles that have passed, and the date of the first one.
     *
     * @return array{count: int, first_due: Carbon|null}
     */
    public static function overdueBillingInfo(Starlink $starlink, ?Carbon $asOf = null): array
    {
        $none = ['count' => 0, 'first_due' => null];
        $today = ($asOf?->copy() ?? now())->startOfDay();

        if (! $starlink->start_date) {
            return $none;
        }
        if ($starlink->advance_payment_until && $starlink->advance_payment_until->gte($today)) {
            return $none;
        }

        $interval = $starlink->billing_interval ?? 'monthly';
        $billingDay = $starlink->start_date->day;

        $anchor = null;
        if ($starlink->last_paid_date) {
            $anchor = $starlink->last_paid_date->copy()->startOfDay();
        }
        if ($starlink->advance_payment_until && $starlink->advance_payment_until->lt($today)) {
            $adv = $starlink->advance_payment_until->copy()->startOfDay();
            if (! $anchor || $adv->gt($anchor)) {
                $anchor = $adv;
            }
        }

        if ($anchor) {
            $check = $anchor->copy();
            if ($interval === 'monthly') {
                $check->addMonth();
                $day = min($billingDay, $check->copy()->endOfMonth()->day);
                $check->day($day);
            } else {
                $check->addYear();
            }
        } else {
            $check = $starlink->start_date->copy()->startOfDay();
        }

        $firstDue = $check->copy();
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

        return ['count' => $count, 'first_due' => $count > 0 ? $firstDue : null];
    }

    /**
     * @return array{
     *     kind: 'overdue'|'upcoming'|null,
     *     overdue_cycles: int,
     *     first_due: Carbon|null,
     *     next_billing_date: Carbon|null,
     *     days_until: int|null,
     *     notify_for_date: Carbon|null
     * }
     */
    public static function billingNotice(Starlink $starlink, ?Carbon $asOf = null): array
    {
        $today = ($asOf?->copy() ?? now())->startOfDay();
        $overdue = self::overdueBillingInfo($starlink, $today);
        $nextBilling = $starlink->next_billing_date?->copy()?->startOfDay();

        if ($overdue['count'] > 0) {
            return [
                'kind' => 'overdue',
                'overdue_cycles' => $overdue['count'],
                'first_due' => $overdue['first_due'],
                'next_billing_date' => $nextBilling,
                'days_until' => $overdue['first_due']
                    ? (int) $today->diffInDays($overdue['first_due'], false)
                    : null,
                'notify_for_date' => $overdue['first_due'] ?? $today,
            ];
        }

        if ($nextBilling) {
            $daysUntil = (int) $today->diffInDays($nextBilling, false);
            $threshold = self::notifyDaysBefore();
            if ($daysUntil >= 0 && $daysUntil <= $threshold) {
                return [
                    'kind' => 'upcoming',
                    'overdue_cycles' => 0,
                    'first_due' => null,
                    'next_billing_date' => $nextBilling,
                    'days_until' => $daysUntil,
                    'notify_for_date' => $nextBilling,
                ];
            }
        }

        return [
            'kind' => null,
            'overdue_cycles' => 0,
            'first_due' => null,
            'next_billing_date' => $nextBilling,
            'days_until' => $nextBilling ? (int) $today->diffInDays($nextBilling, false) : null,
            'notify_for_date' => null,
        ];
    }

    public static function shouldNotifyToday(Starlink $starlink, ?Carbon $asOf = null): bool
    {
        $notice = self::billingNotice($starlink, $asOf);
        if ($notice['kind'] === null || $notice['notify_for_date'] === null) {
            return false;
        }

        $today = ($asOf?->copy() ?? now())->startOfDay();
        $notifyFor = $notice['notify_for_date']->copy()->startOfDay();

        if ($notice['kind'] === 'overdue') {
            // Reminder once per day while still overdue for the same first-due date.
            if ($starlink->last_billing_notified_kind === 'overdue'
                && $starlink->last_billing_notified_for
                && $starlink->last_billing_notified_for->equalTo($notifyFor)
                && $starlink->last_billing_notified_at
                && $starlink->last_billing_notified_at->copy()->startOfDay()->gte($today)
            ) {
                return false;
            }

            return true;
        }

        // Upcoming renewals: one reminder per next billing date.
        if ($starlink->last_billing_notified_kind === 'upcoming'
            && $starlink->last_billing_notified_for
            && $starlink->last_billing_notified_for->equalTo($notifyFor)
        ) {
            return false;
        }

        return true;
    }

    /**
     * @return Collection<int, array{starlink: Starlink, notice: array}>
     */
    public static function dueCandidates(?Carbon $asOf = null): Collection
    {
        $query = Starlink::query()
            ->with(['linkedAccount', 'subscriptionPlanType'])
            ->whereNotNull('start_date');

        if (Schema::hasColumn('starlinks', 'status')) {
            $query->where(function ($q) {
                $q->whereNull('status')
                    ->orWhereRaw('LOWER(status) not in (?, ?)', ['replaced', 'inactive']);
            });
        }

        return $query->orderBy('id')
            ->get()
            ->map(function (Starlink $starlink) use ($asOf) {
                if (! self::shouldNotifyToday($starlink, $asOf)) {
                    return null;
                }

                return [
                    'starlink' => $starlink,
                    'notice' => self::billingNotice($starlink, $asOf),
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * Build a PDF binary for one device billing notice.
     */
    public static function buildDevicePdf(Starlink $starlink, array $notice): string
    {
        return Pdf::loadView('emails.starlink-billing-reminder-pdf', [
            'starlink' => $starlink,
            'notice' => $notice,
        ])->setPaper('a4', 'portrait')->output();
    }

    /**
     * @return array{sent: int, skipped: int, failed: int}
     */
    public static function sendDueReminders(?Carbon $asOf = null): array
    {
        $emails = self::notificationEmails();
        $sent = 0;
        $failed = 0;

        if ($emails === []) {
            return ['sent' => 0, 'skipped' => 0, 'failed' => 0];
        }

        MailConfigService::configure();

        $candidates = self::dueCandidates($asOf);
        $totalActive = Starlink::query()->whereNotNull('start_date')->count();

        foreach ($candidates as $candidate) {
            /** @var Starlink $starlink */
            $starlink = $candidate['starlink'];
            $notice = $candidate['notice'];

            try {
                $pdfBinary = self::buildDevicePdf($starlink, $notice);

                foreach ($emails as $email) {
                    Mail::to($email)->send(new StarlinkBillingReminderMail($starlink, $notice, $pdfBinary));
                }

                $notifyFor = $notice['notify_for_date'];
                $starlink->forceFill([
                    'last_billing_notified_at' => now(),
                    'last_billing_notified_for' => $notifyFor?->toDateString(),
                    'last_billing_notified_kind' => $notice['kind'],
                ])->save();

                $sent++;
            } catch (\Throwable $e) {
                $failed++;
                Log::error('Failed to send Starlink billing reminder', [
                    'starlink_id' => $starlink->id,
                    'device' => $starlink->starlink_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $skipped = max(0, $totalActive - $sent - $failed);

        return compact('sent', 'skipped', 'failed');
    }
}
