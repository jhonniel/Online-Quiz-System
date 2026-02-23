<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LinkedAccount;
use App\Models\Starlink;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StarlinkController extends Controller
{
    private function ensureFullAccess(): void
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403, 'Full admin access required to manage Starlinks.');
        }
    }

    public function index(Request $request)
    {
        $this->ensureFullAccess();

        $query = Starlink::with('linkedAccount')->orderByDesc('created_at');

        $search = $request->input('search');
        if ($search && trim($search) !== '') {
            $term = '%' . trim($search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('account_linked_email', 'like', $term)
                    ->orWhere('starlink_id', 'like', $term)
                    ->orWhere('serial_number', 'like', $term)
                    ->orWhere('kit_number', 'like', $term)
                    ->orWhere('router_id', 'like', $term)
                    ->orWhere('ssid', 'like', $term)
                    ->orWhere('office_location', 'like', $term)
                    ->orWhere('plan', 'like', $term)
                    ->orWhere('status', 'like', $term)
                    ->orWhere('po_no', 'like', $term)
                    ->orWhere('contact_email', 'like', $term)
                    ->orWhere('end_user_email', 'like', $term)
                    ->orWhereHas('linkedAccount', function ($q2) use ($term) {
                        $q2->where('email', 'like', $term)->orWhere('name', 'like', $term);
                    });
            });
        }

        $starlinks = $query->paginate(15)->withQueryString();

        return view('admin.starlinks.index', compact('starlinks', 'search'));
    }

    public function show(Starlink $starlink)
    {
        $this->ensureFullAccess();

        if (request()->wantsJson() || request()->ajax()) {
            $starlink->load('linkedAccount');
            $data = [
                'id' => $starlink->id,
                'linked_account_id' => $starlink->linked_account_id,
                'account_linked_email' => $starlink->account_linked_email,
                'starlink_id' => $starlink->starlink_id,
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
        $this->ensureFullAccess();

        $linkedAccounts = LinkedAccount::orderBy('email')->get();
        $currentLinkedAccountId = null;

        return view('admin.starlinks.create', compact('linkedAccounts', 'currentLinkedAccountId'));
    }

    public function store(Request $request)
    {
        $this->ensureFullAccess();

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
            'po_no' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'plan' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
            'end_user_email' => 'nullable|email|max:255',
        ]);

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
        $this->ensureFullAccess();

        $linkedAccounts = LinkedAccount::orderBy('email')->get();

        // Current linked account: by ID, or by matching account_linked_email to a list account
        $currentLinkedAccountId = $starlink->linked_account_id;
        if ($currentLinkedAccountId === null && ! empty($starlink->account_linked_email)) {
            $currentLinkedAccountId = LinkedAccount::where('email', $starlink->account_linked_email)->value('id');
        }

        return view('admin.starlinks.edit', compact('starlink', 'linkedAccounts', 'currentLinkedAccountId'));
    }

    public function update(Request $request, Starlink $starlink)
    {
        $this->ensureFullAccess();

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
            'po_no' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'plan' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
            'end_user_email' => 'nullable|email|max:255',
        ]);

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
        $this->ensureFullAccess();

        $starlink->delete();

        return redirect()->to('/admin/starlinks')
            ->with('success', 'Starlink device removed.');
    }

    public function importForm()
    {
        $this->ensureFullAccess();

        return view('admin.starlinks.import');
    }

    public function importTemplate(): StreamedResponse
    {
        $this->ensureFullAccess();

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
        $this->ensureFullAccess();

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
        if (! empty($errors)) {
            $message .= ' ' . count($errors) . ' row(s) had errors: ' . implode('; ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= '…';
            }
        }

        return redirect()->to('/admin/starlinks')
            ->with($created > 0 ? 'success' : 'warning', $message);
    }
}
