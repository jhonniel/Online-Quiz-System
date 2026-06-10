<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\EmployeePayslip;
use App\Support\PayslipPdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PayslipController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user->role === 'employee', 403);

        $payslips = EmployeePayslip::query()
            ->where('user_id', $user->id)
            ->orderByDesc('period_end')
            ->orderByDesc('id')
            ->paginate(10);

        return view('user.payslips.index', compact('payslips', 'user'));
    }

    public function show(Request $request, EmployeePayslip $payslip)
    {
        $user = $request->user();
        abort_unless($user->role === 'employee', 403);
        abort_unless((int) $payslip->user_id === (int) $user->id, 403);

        $payslip->load(['employee:id,name,department_id,date_hired,e_signature_path,p12_certificate_path', 'employee.department:id,name', 'employee.departmentPosition:id,name']);
        $payslip->syncProfileFieldsFromEmployee($user);

        return view('user.payslips.show', compact('payslip', 'user'));
    }

    public function sign(Request $request, EmployeePayslip $payslip)
    {
        try {
            $user = $request->user();
            abort_unless($user->role === 'employee', 403);
            abort_unless((int) $payslip->user_id === (int) $user->id, 403);

            if (! $user->hasESignature()) {
                return $this->signResponse($request, false, 'Upload your e-signature on your profile before signing payslips.', 422);
            }

            if (empty($user->p12_certificate_path)) {
                return $this->signResponse($request, false, 'Upload your P12 certificate on your profile before signing payslips.', 422);
            }

            $request->validate([
                'p12_certificate_password' => 'required|string|max:255',
            ], [
                'p12_certificate_password.required' => 'P12 certificate password is required.',
            ]);

            $password = (string) $request->input('p12_certificate_password');
            $contents = $this->readP12Contents((string) $user->p12_certificate_path);
            $certs = [];

            if ($contents === null || ! openssl_pkcs12_read($contents, $certs, $password)) {
                throw ValidationException::withMessages([
                    'p12_certificate_password' => ['The P12 certificate password is incorrect.'],
                ]);
            }

            $user->loadMissing('department:id,name,position');

            $signedAt = now();
            $pdfBinary = PayslipPdf::renderSignedPdfBinary($payslip, $user, $signedAt, $password);

            if ($payslip->signed_document_path) {
                $this->deleteStoredPdf((string) $payslip->signed_document_path, (string) ($payslip->storage_disk ?? ''));
            }

            [$storedPath, $disk] = $this->storePdfFile($pdfBinary, (int) $user->id, (int) $payslip->id);

            $payslip->update([
                'signed_at' => $signedAt,
                'signed_document_path' => $storedPath,
                'storage_disk' => $disk,
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payslip generated and signed successfully.',
                    'redirect_url' => route('user.payslips.show', $payslip),
                ]);
            }

            return redirect()
                ->route('user.payslips.show', $payslip)
                ->with('success', 'Payslip generated and signed successfully.');
        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => collect($e->errors())->flatten()->first() ?: 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }

            throw $e;
        } catch (\Throwable $e) {
            return $this->signResponse($request, false, 'Failed to sign payslip: '.$e->getMessage(), 500);
        }
    }

    public function signedPdf(Request $request, EmployeePayslip $payslip)
    {
        $user = $request->user();
        abort_unless($user->role === 'employee', 403);
        abort_unless((int) $payslip->user_id === (int) $user->id, 403);
        abort_unless($payslip->isSigned(), 404);

        $disk = $this->resolveDiskForPath(
            (string) $payslip->signed_document_path,
            (string) ($payslip->storage_disk ?? '')
        );
        abort_if($disk === null, 404);

        $contents = Storage::disk($disk)->get((string) $payslip->signed_document_path);
        abort_if(! is_string($contents), 404);

        $filename = 'payslip-'.$payslip->period_start->format('Y-m-d').'-signed.pdf';
        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        return response($contents, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition.'; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function storePdfFile(string $pdfBinary, int $userId, int $payslipId): array
    {
        $dir = 'employee-signed-payslips';
        $doConfigured = ! empty(env('DIGITALOCEAN_SPACES_KEY') ?: env('DO_SPACES_KEY'))
            && ! empty(env('DIGITALOCEAN_SPACES_SECRET') ?: env('DO_SPACES_SECRET'))
            && ! empty(env('DIGITALOCEAN_SPACES_BUCKET') ?: env('DO_SPACES_BUCKET'));

        if ($doConfigured) {
            $assetRoot = trim(env('DIGITALOCEAN_SPACES_ROOT_PATH', ''), '/');
            $dir = $assetRoot ? $assetRoot.'/'.$dir : $dir;
        }

        $disk = $doConfigured ? 'digitalocean' : 'public';
        $filename = $userId.'-'.$payslipId.'-'.now()->format('YmdHis').'.pdf';
        $path = $dir.'/'.$filename;

        Storage::disk($disk)->put($path, $pdfBinary);

        return [$path, $disk];
    }

    private function deleteStoredPdf(string $path, string $preferredDisk = ''): void
    {
        if ($path === '') {
            return;
        }

        foreach (array_values(array_unique(array_filter([
            $preferredDisk,
            'digitalocean',
            'public',
            config('filesystems.default', 'local'),
        ]))) as $disk) {
            try {
                if (Storage::disk($disk)->exists($path)) {
                    Storage::disk($disk)->delete($path);

                    return;
                }
            } catch (\Throwable) {
                continue;
            }
        }
    }

    private function resolveDiskForPath(string $path, string $preferredDisk = ''): ?string
    {
        foreach (array_values(array_unique(array_filter([
            $preferredDisk,
            'digitalocean',
            'public',
            'local',
        ]))) as $disk) {
            try {
                if (Storage::disk($disk)->exists($path)) {
                    return $disk;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
    }

    private function readP12Contents(string $path): ?string
    {
        foreach (['digitalocean', 'public', 'local', config('filesystems.default', 'local')] as $diskName) {
            try {
                $disk = Storage::disk($diskName);
                if ($disk->exists($path)) {
                    $contents = $disk->get($path);

                    return is_string($contents) && $contents !== '' ? $contents : null;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
    }

    private function signResponse(Request $request, bool $success, string $message, int $status = 200)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => $success,
                'message' => $message,
            ], $status);
        }

        return redirect()
            ->route('user.payslips.index')
            ->with($success ? 'success' : 'error', $message);
    }
}
