<?php

namespace App\Http\Controllers;

use App\Mail\TicketReportReceived;
use App\Mail\TicketReportAdminNotification;
use App\Models\Setting;
use App\Models\TicketProblemType;
use App\Models\TicketReport;
use App\Models\User;
use App\Services\MailConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ReportProblemController extends Controller
{
    public function show(Request $request)
    {
        $problemTypes = TicketReport::problemTypes();

        $ticket = null;
        $ticketLookupNumber = null;
        $ticketLookupError = null;

        $rawTicketNumber = trim((string) $request->query('ticket_number', ''));
        if ($rawTicketNumber !== '') {
            $ticketLookupNumber = Str::upper($rawTicketNumber);
            $ticket = TicketReport::where('ticket_number', $ticketLookupNumber)->first();

            if (! $ticket) {
                $ticketLookupError = 'Ticket number not found. Please check and try again.';
            }
        }

        return view('report-problem.form', compact(
            'problemTypes',
            'ticket',
            'ticketLookupNumber',
            'ticketLookupError'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:' . implode(',', TicketProblemType::pluck('slug')->all()),
            'description' => 'required|string|max:5000',
            'full_name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:50',
            'email' => 'required|email',
            'office' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $path = 'ticket-reports/' . now()->format('Y/m/d') . '/' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
            Storage::disk('digitalocean')->put($path, file_get_contents($file->getRealPath()), 'public');
            $imagePath = $path;
        }

        $ticket = TicketReport::create([
            'ticket_number' => TicketReport::generateTicketNumber(),
            'type' => $validated['type'],
            'description' => $validated['description'],
            'full_name' => $validated['full_name'],
            'contact_number' => $validated['contact_number'] ?? null,
            'email' => $validated['email'],
            'office' => $validated['office'] ?? null,
            'address' => $validated['address'] ?? null,
            'image_path' => $imagePath,
            'status' => TicketReport::STATUS_OPEN,
            'payment_status' => TicketReport::PAYMENT_STATUS_PENDING,
        ]);

        $emailSent = false;
        $adminNotificationSentCount = 0;
        try {
            MailConfigService::configure();
            Mail::to($ticket->email)->send(new TicketReportReceived($ticket));
            Log::info('Ticket report confirmation sent', ['ticket' => $ticket->ticket_number, 'to' => $ticket->email]);
            $emailSent = true;
        } catch (\Throwable $e) {
            Log::warning('Ticket report confirmation email failed: ' . $e->getMessage(), [
                'ticket' => $ticket->ticket_number,
                'to' => $ticket->email,
                'exception' => $e,
            ]);
            report($e);
        }

        try {
            $adminEmails = $this->resolveTicketAdminNotificationEmails();
            foreach ($adminEmails as $adminEmail) {
                Mail::to($adminEmail)->send(new TicketReportAdminNotification($ticket));
                $adminNotificationSentCount++;
            }

            Log::info('Ticket report admin notifications sent', [
                'ticket' => $ticket->ticket_number,
                'recipients' => $adminEmails,
                'sent_count' => $adminNotificationSentCount,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Ticket report admin notification email failed: ' . $e->getMessage(), [
                'ticket' => $ticket->ticket_number,
                'sent_count' => $adminNotificationSentCount,
                'exception' => $e,
            ]);
            report($e);
        }

        $successMsg = 'Your report has been submitted. Your ticket number is: ' . $ticket->ticket_number;
        $successMsg .= $emailSent ? '. We have sent a confirmation to your email.' : '. A confirmation email could not be sent.';
        return redirect()->back()->with('success', $successMsg);
    }

    /**
     * Get admin notification recipients for report-problem submissions.
     */
    protected function resolveTicketAdminNotificationEmails(): array
    {
        $emailsRaw = (string) Setting::get('ticket_admin_notification_email', '');

        if (trim($emailsRaw) === '') {
            $emailsRaw = (string) Setting::get('leave_admin_notification_email', '');
        }

        $emails = [];
        if (trim($emailsRaw) !== '') {
            $emails = array_filter(array_map('trim', explode(',', $emailsRaw)));
        }

        if (empty($emails)) {
            $emails = User::query()
                ->where('role', 'admin')
                ->whereNotNull('email')
                ->pluck('email')
                ->map(fn ($email) => trim((string) $email))
                ->filter()
                ->values()
                ->all();
        }

        return array_values(array_unique(array_filter($emails, fn ($email) => (bool) filter_var($email, FILTER_VALIDATE_EMAIL))));
    }
}
