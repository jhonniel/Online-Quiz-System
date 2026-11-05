<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OmadaRequest;
use App\Models\LinkedAccount;
use App\Models\Omada;
use App\Models\SubscriptionPlanType;
use App\Rules\UniqueDeviceIdentifier;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OmadaController extends Controller
{
    /**
     * Allow access for users with Linked Accounts permission (route uses admin.permission:linked_accounts).
     */
    private function ensureCanAccess(): void
    {
        if (auth()->user()->canAccessLinkedAccounts()) {
            return;
        }
        abort(403, 'You do not have permission to manage Omada.');
    }

    public function index(Request $request)
    {
        $this->ensureCanAccess();

        $today = now()->startOfDay()->format('Y-m-d');
        $query = Omada::orderByRaw("CASE WHEN license_expiration IS NOT NULL AND license_expiration >= ? THEN 0 ELSE 1 END", [$today])
            ->orderByDesc('created_at');

        $search = $request->input('search');
        if ($search && trim($search) !== '') {
            $term = '%' . trim($search) . '%';
            $query->where(function ($q) use ($term, $search) {
                $q->where('account_linked_email', 'like', $term)
                    ->orWhere('site', 'like', $term)
                    ->orWhere('office', 'like', $term)
                    ->orWhere('type', 'like', $term)
                    ->orWhere('serial_number', 'like', $term)
                    ->orWhere('mac_address', 'like', $term)
                    ->orWhere('license', 'like', $term);
                // Status is derived; match "active" / "expired" by license_expiration
                $lower = strtolower(trim($search));
                if ($lower === 'active') {
                    $q->orWhere(function ($q2) {
                        $q2->whereNotNull('license_expiration')->where('license_expiration', '>=', now()->startOfDay());
                    });
                } elseif ($lower === 'expired') {
                    $q->orWhere(function ($q2) {
                        $q2->whereNull('license_expiration')->orWhere('license_expiration', '<', now()->startOfDay());
                    });
                }
            });
        }

        $omadas = $query->paginate(15)->withQueryString();

        return view('admin.omadas.index', compact('omadas', 'search'));
    }

    public function show(Omada $omada)
    {
        $this->ensureCanAccess();

        if (request()->wantsJson() || request()->ajax()) {
            $data = $omada->toArray();
            $data['license_expiration_formatted'] = $omada->license_expiration ? $omada->license_expiration->format('M j, Y') : null;
            $data['status'] = $omada->status;
            return response()->json($data);
        }

        return redirect()->to('/admin/omadas');
    }

    public function create()
    {
        $this->ensureCanAccess();

        $subscriptionPlanTypes = SubscriptionPlanType::where('subscription_type', 'omada')->orderBy('name')->get();

        return view('admin.omadas.create', compact('subscriptionPlanTypes'));
    }

    public function store(OmadaRequest $request)
    {
        $this->ensureCanAccess();

        $validated = $request->validated();

        $validated['subscription_plan_type_id'] = ! empty($validated['subscription_plan_type_id']) ? $validated['subscription_plan_type_id'] : null;

        // Ensure a linked account exists for the email so the dashboard shows data
        if (! empty($validated['account_linked_email'])) {
            LinkedAccount::firstOrCreate(
                ['email' => $validated['account_linked_email']],
                ['email' => $validated['account_linked_email']]
            );
        }

        Omada::create($validated);

        return redirect()->to('/admin/omadas')
            ->with('success', 'Omada device added successfully.');
    }

    public function edit(Omada $omada)
    {
        $this->ensureCanAccess();

        $subscriptionPlanTypes = SubscriptionPlanType::where('subscription_type', 'omada')->orderBy('name')->get();

        return view('admin.omadas.edit', compact('omada', 'subscriptionPlanTypes'));
    }

    public function update(OmadaRequest $request, Omada $omada)
    {
        $this->ensureCanAccess();

        $validated = $request->validated();

        $validated['subscription_plan_type_id'] = ! empty($validated['subscription_plan_type_id']) ? $validated['subscription_plan_type_id'] : null;

        // Ensure a linked account exists for the email so the dashboard shows data
        if (! empty($validated['account_linked_email'])) {
            LinkedAccount::firstOrCreate(
                ['email' => $validated['account_linked_email']],
                ['email' => $validated['account_linked_email']]
            );
        }

        $omada->update($validated);

        return redirect()->to('/admin/omadas')
            ->with('success', 'Omada device updated successfully.');
    }

    public function destroy(Omada $omada)
    {
        $this->ensureCanAccess();

        $omada->delete();

        return redirect()->to('/admin/omadas')
            ->with('success', 'Omada device removed.');
    }

    public function importForm()
    {
        $this->ensureCanAccess();

        return view('admin.omadas.import');
    }

    public function importTemplate(): StreamedResponse
    {
        $this->ensureCanAccess();

        $headers = [
            'account_linked_email',
            'site',
            'office',
            'type',
            'serial_number',
            'mac_address',
            'license',
            'license_expiration',
        ];

        return new StreamedResponse(function () use ($headers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            fputcsv($out, [
                'account@example.com',
                'Site A',
                'Main Office',
                'Controller',
                'SN-OMADA-001',
                '00:11:22:33:44:55',
                'Standard',
                '2025-12-31',
            ]);
            fclose($out);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="omadas_import_template.csv"',
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
            return redirect()->to('/admin/omadas/import')
                ->with('error', 'Could not read the uploaded file.');
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            return redirect()->to('/admin/omadas/import')
                ->with('error', 'The CSV file is empty or invalid.');
        }

        $expected = ['account_linked_email', 'site', 'office', 'type', 'serial_number', 'mac_address', 'license', 'license_expiration'];
        $header = array_map('trim', $header);
        $missing = array_diff($expected, $header);
        if (! empty($missing)) {
            fclose($handle);
            return redirect()->to('/admin/omadas/import')
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

            if (trim((string) ($data['serial_number'] ?? '')) === '') {
                $errors[] = "Row {$rowNum}: Serial number required.";
                continue;
            }

            // Skip row if a device with same Serial number, MAC address or License already exists
            $alreadyExists = false;
            foreach (array_keys(OmadaRequest::UNIQUE_FIELDS) as $field) {
                if (UniqueDeviceIdentifier::isTaken('omadas', $field, $data[$field] ?? null)) {
                    $alreadyExists = true;
                    break;
                }
            }
            if ($alreadyExists) {
                $skipped++;
                continue;
            }

            if (! empty($data['account_linked_email'])) {
                LinkedAccount::firstOrCreate(
                    ['email' => $data['account_linked_email']],
                    ['email' => $data['account_linked_email']]
                );
            }
            $data['license_expiration'] = ! empty($data['license_expiration']) ? $data['license_expiration'] : null;

            try {
                Omada::create($data);
                $created++;
            } catch (\Throwable $e) {
                $errors[] = "Row {$rowNum}: " . $e->getMessage();
            }
        }
        fclose($handle);

        $message = $created > 0
            ? "Imported {$created} Omada device(s) successfully."
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

        return redirect()->to('/admin/omadas')
            ->with($created > 0 ? 'success' : 'warning', $message);
    }
}
