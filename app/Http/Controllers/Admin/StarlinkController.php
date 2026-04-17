<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LinkedAccount;
use App\Models\Starlink;
use App\Models\SubscriptionPlanType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StarlinkController extends Controller
{
    /**
     * Allow access for users with Linked Accounts permission (route uses admin.permission:linked_accounts).
     */
    private function ensureCanAccess(): void
    {
        if (auth()->user()->canAccessLinkedAccounts()) {
            return;
        }
        abort(403, 'You do not have permission to manage Starlinks.');
    }

    public function index(Request $request)
    {
        $this->ensureCanAccess();

        $query = Starlink::with('linkedAccount')->orderByDesc('created_at');
        $dbDriver = DB::connection()->getDriverName();
        $idLikeSql = $dbDriver === 'pgsql' ? 'CAST(id AS TEXT) LIKE ?' : 'CAST(id AS CHAR) LIKE ?';

        $search = $request->input('search');
        if ($search && trim($search) !== '') {
            // Split by spaces so users can type natural multi-word queries.
            // Each term must match at least one searchable field.
            $tokens = preg_split('/\s+/', trim($search)) ?: [];
            foreach ($tokens as $token) {
                $token = trim($token);
                if ($token === '') {
                    continue;
                }

                $term = '%' . $token . '%';
                $query->where(function ($q) use ($term, $token, $idLikeSql) {
                    $q->whereRaw($idLikeSql, [$term])
                        // Explicit primary fields from the listing table.
                        ->orWhereRaw('LOWER(COALESCE(account_linked_email, \'\')) LIKE LOWER(?)', [$term]) // Account / Email
                        ->orWhereRaw('LOWER(COALESCE(starlink_id, \'\')) LIKE LOWER(?)', [$term]) // Starlink ID
                        ->orWhereRaw('LOWER(COALESCE(serial_number, \'\')) LIKE LOWER(?)', [$term]) // Serial number
                        ->orWhereRaw('LOWER(COALESCE(kit_number, \'\')) LIKE LOWER(?)', [$term]) // Kit number
                        ->orWhereRaw('LOWER(COALESCE(router_id, \'\')) LIKE LOWER(?)', [$term]) // Router ID
                        ->orWhereRaw('LOWER(COALESCE(office_location, \'\')) LIKE LOWER(?)', [$term]) // Office / location
                        // Additional searchable fields.
                        ->orWhereRaw('LOWER(COALESCE(ssid, \'\')) LIKE LOWER(?)', [$term])
                        ->orWhereRaw('LOWER(COALESCE(wifi_password, \'\')) LIKE LOWER(?)', [$term])
                        ->orWhereRaw('LOWER(COALESCE(plan, \'\')) LIKE LOWER(?)', [$term])
                        ->orWhereRaw('LOWER(COALESCE(status, \'\')) LIKE LOWER(?)', [$term])
                        ->orWhereRaw('LOWER(COALESCE(po_no, \'\')) LIKE LOWER(?)', [$term])
                        ->orWhereRaw('LOWER(COALESCE(contact_email, \'\')) LIKE LOWER(?)', [$term])
                        ->orWhereRaw('LOWER(COALESCE(end_user_email, \'\')) LIKE LOWER(?)', [$term])
                        ->orWhereRaw('LOWER(COALESCE(billing_interval, \'\')) LIKE LOWER(?)', [$term])
                        ->orWhereHas('linkedAccount', function ($q2) use ($term) {
                            $q2->whereRaw('LOWER(COALESCE(email, \'\')) LIKE LOWER(?)', [$term])
                                ->orWhereRaw('LOWER(COALESCE(name, \'\')) LIKE LOWER(?)', [$term]);
                        });

                    // Exact ID / date matching (database-agnostic helpers)
                    if (ctype_digit($token)) {
                        $q->orWhere('id', (int) $token);
                    }
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $token)) {
                        $q->orWhereDate('start_date', $token)
                            ->orWhereDate('last_paid_date', $token)
                            ->orWhereDate('advance_payment_until', $token);
                    }
                });
            }
        }

        $starlinks = $query->paginate(15)->withQueryString();

        // Which starlinks are currently overdue (past billing date, not yet paid)
        $today = now()->startOfDay();
        $overdueStarlinks = Starlink::with('linkedAccount')
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
        $overdueCounts = [];
        foreach ($overdueStarlinks as $s) {
            $overdueCounts[$s->id] = 1; // Currently overdue
        }

        return view('admin.starlinks.index', compact('starlinks', 'search', 'overdueCounts'));
    }

    public function show(Starlink $starlink)
    {
        $this->ensureCanAccess();

        if (request()->wantsJson() || request()->ajax()) {
            $starlink->load('linkedAccount');
            $data = [
                'id' => $starlink->id,
                'linked_account_id' => $starlink->linked_account_id,
                'account_linked_email' => $starlink->account_linked_email,
                'starlink_id' => $starlink->starlink_id,
                'overdue_billing_count' => $this->getOverdueCycleCount($starlink),
                'serial_number' => $starlink->serial_number,
                'kit_number' => $starlink->kit_number,
                'router_id' => $starlink->router_id,
                'ssid' => $starlink->ssid,
                'wifi_password' => $starlink->wifi_password,
                'office_location' => $starlink->office_location,
                'start_date' => $starlink->start_date?->format('Y-m-d'),
                'po_no' => $starlink->po_no,
                'contact_email' => $starlink->contact_email,
                'plan' => $starlink->plan,
                'status' => $starlink->status,
                'end_user_email' => $starlink->end_user_email,
                'created_at' => $starlink->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $starlink->updated_at?->format('Y-m-d H:i:s'),
            ];
            $data['account_display'] = $starlink->account_linked_email ?: ($starlink->linkedAccount?->email ?? '—');
            $data['start_date_formatted'] = $starlink->start_date ? $starlink->start_date->format('M j, Y') : null;
            return response()->json($data);
        }

        $starlink->load('linkedAccount');
        return view('admin.starlinks.show', compact('starlink'));
    }

    public function create()
    {
        $this->ensureCanAccess();

        $linkedAccounts = LinkedAccount::orderBy('email')->get();
        $subscriptionPlanTypes = SubscriptionPlanType::where('subscription_type', 'starlink')->orderBy('name')->get();
        $currentLinkedAccountId = null;

        return view('admin.starlinks.create', compact('linkedAccounts', 'subscriptionPlanTypes', 'currentLinkedAccountId'));
    }

    public function store(Request $request)
    {
        $this->ensureCanAccess();

        $validated = $request->validate([
            'linked_account_id' => 'nullable|exists:linked_accounts,id',
            'account_linked_email' => 'nullable|string|max:255',
            'starlink_id' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'kit_number' => 'nullable|string|max:255',
            'router_id' => 'nullable|string|max:255',
            'ssid' => 'nullable|string|max:255',
            'wifi_password' => 'nullable|string|max:255',
            'office_location' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'advance_payment_until' => 'nullable|date',
            'last_paid_date' => 'nullable|date',
            'billing_interval' => 'nullable|string|in:monthly,yearly',
            'po_no' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'plan' => 'nullable|string|max:255',
            'subscription_plan_type_id' => 'nullable|exists:subscription_plan_types,id',
            'status' => 'nullable|string|max:50',
            'end_user_email' => 'nullable|email|max:255',
        ]);
        $validated['subscription_plan_type_id'] = ! empty($validated['subscription_plan_type_id']) ? (int) $validated['subscription_plan_type_id'] : null;

        // Keep plain "plan" column in sync with selected subscription plan type
        // because list/details/search currently read from starlinks.plan.
        if (! empty($validated['subscription_plan_type_id'])) {
            $selectedPlanName = SubscriptionPlanType::where('id', $validated['subscription_plan_type_id'])->value('name');
            if ($selectedPlanName) {
                $validated['plan'] = $selectedPlanName;
            }
        } elseif (empty($validated['plan'])) {
            $validated['plan'] = null;
        }

        // Check if a device with the same Starlink ID, Serial number, Kit number, or Router ID already exists
        $deviceFields = [
            'starlink_id' => 'Starlink ID',
            'serial_number' => 'Serial number',
            'kit_number' => 'Kit number',
            'router_id' => 'Router ID',
        ];
        $duplicateErrors = [];
        foreach ($deviceFields as $field => $label) {
            $value = isset($validated[$field]) ? trim((string) $validated[$field]) : '';
            if ($value !== '' && Starlink::where($field, $value)->exists()) {
                $duplicateErrors[$field] = "A device with this {$label} already exists.";
            }
        }
        if (! empty($duplicateErrors)) {
            throw ValidationException::withMessages($duplicateErrors);
        }

        // Ensure a linked account exists for the email so the dashboard shows data
        if (! empty($validated['account_linked_email'])) {
            $account = LinkedAccount::firstOrCreate(
                ['email' => $validated['account_linked_email']],
                ['email' => $validated['account_linked_email']]
            );
            $validated['linked_account_id'] = $validated['linked_account_id'] ?? $account->id;
        }

        Starlink::create($validated);

        return redirect()->to('/admin/starlinks')
            ->with('success', 'Starlink device added successfully.');
    }

    public function edit(Starlink $starlink)
    {
        $this->ensureCanAccess();

        $linkedAccounts = LinkedAccount::orderBy('email')->get();

        // Current linked account: by ID, or by matching account_linked_email to a list account
        $currentLinkedAccountId = $starlink->linked_account_id;
        if ($currentLinkedAccountId === null && ! empty($starlink->account_linked_email)) {
            $currentLinkedAccountId = LinkedAccount::where('email', $starlink->account_linked_email)->value('id');
        }

        $subscriptionPlanTypes = SubscriptionPlanType::where('subscription_type', 'starlink')->orderBy('name')->get();

        return view('admin.starlinks.edit', compact('starlink', 'linkedAccounts', 'subscriptionPlanTypes', 'currentLinkedAccountId'));
    }

    public function update(Request $request, Starlink $starlink)
    {
        $this->ensureCanAccess();

        $validated = $request->validate([
            'linked_account_id' => 'nullable|exists:linked_accounts,id',
            'account_linked_email' => 'nullable|string|max:255',
            'starlink_id' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'kit_number' => 'nullable|string|max:255',
            'router_id' => 'nullable|string|max:255',
            'ssid' => 'nullable|string|max:255',
            'wifi_password' => 'nullable|string|max:255',
            'office_location' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'advance_payment_until' => 'nullable|date',
            'last_paid_date' => 'nullable|date',
            'billing_interval' => 'nullable|string|in:monthly,yearly',
            'po_no' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'plan' => 'nullable|string|max:255',
            'subscription_plan_type_id' => 'nullable|exists:subscription_plan_types,id',
            'status' => 'nullable|string|max:50',
            'end_user_email' => 'nullable|email|max:255',
        ]);

        $validated['subscription_plan_type_id'] = ! empty($validated['subscription_plan_type_id']) ? $validated['subscription_plan_type_id'] : null;
        // Keep plain "plan" column in sync with selected subscription plan type
        // because list/details/search currently read from starlinks.plan.
        if (! empty($validated['subscription_plan_type_id'])) {
            $selectedPlanName = SubscriptionPlanType::where('id', $validated['subscription_plan_type_id'])->value('name');
            if ($selectedPlanName) {
                $validated['plan'] = $selectedPlanName;
            }
        } elseif (empty($validated['plan'])) {
            $validated['plan'] = null;
        }

        // Ensure a linked account exists for the email so the dashboard shows data
        if (! empty($validated['account_linked_email'])) {
            $account = LinkedAccount::firstOrCreate(
                ['email' => $validated['account_linked_email']],
                ['email' => $validated['account_linked_email']]
            );
            $validated['linked_account_id'] = $validated['linked_account_id'] ?? $account->id;
        }

        $starlink->update($validated);

        return redirect()->to('/admin/starlinks')
            ->with('success', 'Starlink device updated successfully.');
    }

    public function destroy(Starlink $starlink)
    {
        $this->ensureCanAccess();

        $starlink->delete();

        return redirect()->to('/admin/starlinks')
            ->with('success', 'Starlink device removed.');
    }

    public function importForm()
    {
        $this->ensureCanAccess();

        return view('admin.starlinks.import');
    }

    public function importTemplate(): StreamedResponse
    {
        $this->ensureCanAccess();

        $headers = [
            'account_linked_email',
            'starlink_id',
            'serial_number',
            'kit_number',
            'router_id',
            'ssid',
            'wifi_password',
            'office_location',
            'start_date',
            'po_no',
            'contact_email',
            'plan',
            'status',
            'end_user_email',
        ];

        return new StreamedResponse(function () use ($headers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            // One sample row with hints (start_date format: Y-m-d)
            fputcsv($out, [
                'account@example.com',
                'STARLINK-001',
                'SN12345',
                'KIT-001',
                'ROUTER-01',
                'MySSID',
                'wifipassword',
                'Main Office',
                '2024-01-15',
                'PO-123',
                'contact@example.com',
                'Business',
                'Active',
                'enduser@example.com',
            ]);
            fclose($out);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="starlinks_import_template.csv"',
        ]);
    }

    public function processImport(Request $request)
    {
        $this->ensureCanAccess();

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            return redirect()->to('/admin/starlinks/import')
                ->with('error', 'Could not read the uploaded file.');
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            return redirect()->to('/admin/starlinks/import')
                ->with('error', 'The CSV file is empty or invalid.');
        }

        $expected = ['account_linked_email', 'starlink_id', 'serial_number', 'kit_number', 'router_id', 'ssid', 'wifi_password', 'office_location', 'start_date', 'po_no', 'contact_email', 'plan', 'status', 'end_user_email'];
        $header = array_map('trim', $header);
        $missing = array_diff($expected, $header);
        if (! empty($missing)) {
            fclose($handle);
            return redirect()->to('/admin/starlinks/import')
                ->with('error', 'Invalid CSV columns. Download the sample template and use the same headers: ' . implode(', ', $expected));
        }

        $created = 0;
        $skipped = 0;
        $errors = [];
        $rowNum = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            $assoc = array_combine($header, array_pad($row, count($header), null));
            if ($assoc === false) {
                continue;
            }
            $assoc = array_map('trim', $assoc);
            if (empty(array_filter($assoc))) {
                continue;
            }

            $data = [];
            foreach ($expected as $key) {
                $data[$key] = $assoc[$key] ?? null;
            }

            // Skip row if a device with same Starlink ID, Serial number, Kit number, or Router ID already exists
            $alreadyExists = false;
            foreach (['starlink_id', 'serial_number', 'kit_number', 'router_id'] as $field) {
                $value = isset($data[$field]) ? trim((string) $data[$field]) : '';
                if ($value !== '' && Starlink::where($field, $value)->exists()) {
                    $alreadyExists = true;
                    break;
                }
            }
            if ($alreadyExists) {
                $skipped++;
                continue;
            }

            if (! empty($data['account_linked_email'])) {
                $account = LinkedAccount::firstOrCreate(
                    ['email' => $data['account_linked_email']],
                    ['email' => $data['account_linked_email']]
                );
                $data['linked_account_id'] = $account->id;
            }
            $data['start_date'] = ! empty($data['start_date']) ? $data['start_date'] : null;

            try {
                Starlink::create($data);
                $created++;
            } catch (\Throwable $e) {
                $errors[] = "Row {$rowNum}: " . $e->getMessage();
            }
        }
        fclose($handle);

        $message = $created > 0
            ? "Imported {$created} Starlink device(s) successfully."
            : 'No devices were imported.';
        if ($skipped > 0) {
            $message .= " {$skipped} row(s) skipped (device already exists).";
        }
        if (! empty($errors)) {
            $message .= ' ' . count($errors) . ' row(s) had errors: ' . implode('; ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= '…';
            }
        }

        return redirect()->to('/admin/starlinks')
            ->with($created > 0 ? 'success' : 'warning', $message);
    }

    /**
     * First unpaid billing date (from last_paid_date + 1 period, or start_date if never paid).
     */
    private function getCurrentPeriodBillingDate(Starlink $s): ?\DateTimeInterface
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
     * Count how many billing cycles have passed but not been paid.
     * Based on last_paid_date (or advance_payment_until if it extends further) — counts cycles not marked as paid.
     */
    private function getOverdueCycleCount(Starlink $s): int
    {
        $today = now()->startOfDay();
        if (! $s->start_date) {
            return 0;
        }
        if ($s->advance_payment_until && $s->advance_payment_until->gte($today)) {
            return 0;
        }

        $interval = $s->billing_interval ?? 'monthly';
        $billingDay = $s->start_date->day;

        // Anchor = most recent date covered (last_paid_date or advance_payment_until, whichever is later)
        $anchor = null;
        if ($s->last_paid_date) {
            $anchor = $s->last_paid_date->copy()->startOfDay();
        }
        if ($s->advance_payment_until && $s->advance_payment_until->lt($today)) {
            $adv = $s->advance_payment_until->copy()->startOfDay();
            if (! $anchor || $adv->gt($anchor)) {
                $anchor = $adv;
            }
        }

        $count = 0;
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
            $check = $s->start_date->copy()->startOfDay();
        }

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
}
