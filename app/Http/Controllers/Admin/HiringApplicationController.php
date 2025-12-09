<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HiringApplication;
use App\Services\MailConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class HiringApplicationController extends Controller
{
    public function index(Request $request)
    {
        $query = HiringApplication::with(['reviewer', 'user', 'hiringPosition']);

        // Filter by position if provided
        if ($request->has('position') && $request->position) {
            $query->where('hiring_position_id', $request->position);
        }

        // Get per page value (default 20, options: 10, 20, 50, 100)
        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 20;

        $applications = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        $positionFilter = $request->position;
        $positions = \App\Models\HiringPosition::orderBy('title')->get();

        $baseQuery = HiringApplication::query();
        if ($positionFilter) {
            $baseQuery->where('hiring_position_id', $positionFilter);
        }

        $stats = [
            'total' => $baseQuery->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'accepted' => (clone $baseQuery)->where('status', 'accepted')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
            'interview_scheduled' => (clone $baseQuery)->where('status', 'interview_scheduled')->count(),
        ];

        return view('admin.hiring-applications.index', compact('applications', 'stats', 'positions', 'positionFilter', 'perPage'));
    }

    public function show(HiringApplication $application)
    {
        $application->load(['reviewer', 'user', 'hiringPosition']);
        return view('admin.hiring-applications.show', compact('application'));
    }

    public function accept(Request $request, HiringApplication $application)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
            'interview_date' => 'required|date|after_or_equal:today',
        ]);

        // Generate a random password for the applicant
        $password = \Illuminate\Support\Str::random(12);

        // Check if user already exists with this email
        $user = \App\Models\User::where('email', $application->email)->first();

        if (!$user) {
            // Create new user account with role 'applicant'
            $user = \App\Models\User::create([
                'name' => $application->full_name,
                'email' => $application->email,
                'password' => \Illuminate\Support\Facades\Hash::make($password),
                'role' => 'applicant',
                'is_active' => true,
                'is_approved' => true,
            ]);
        } else {
            // Update existing user to applicant role and activate
            $user->update([
                'role' => 'applicant',
                'is_active' => true,
                'is_approved' => true,
                'password' => \Illuminate\Support\Facades\Hash::make($password), // Reset password
            ]);
        }

        // Update application
        $application->update([
            'status' => 'accepted',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'interview_date' => $request->interview_date,
            'user_id' => $user->id,
        ]);

        // Generate acceptance token (for backward compatibility)
        $token = $application->generateAcceptanceToken();

        // Send email with credentials
        $emailNotificationsEnabled = \App\Models\Setting::get('hiring_email_notifications', 'enabled');
        if ($emailNotificationsEnabled === 'enabled') {
            try {
                // Ensure mail configuration is up to date from settings
                MailConfigService::configure();

                Mail::to($application->email)
                    ->send(new \App\Mail\HiringApplicationCredentials(
                        $application,
                        $application->email,
                        $password,
                        $request->interview_date,
                        $application->hiringPosition
                    ));
            } catch (\Exception $e) {
                Log::error('Failed to send credentials email', [
                    'error' => $e->getMessage(),
                    'application_id' => $application->id
                ]);
            }
        }

        return redirect()->route('admin.hiring-applications.show', $application)
            ->with('success', 'Application accepted. User account created and credentials sent via email.');
    }

    public function reject(Request $request, HiringApplication $application)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $application->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Send email notification to applicant if enabled
        $emailNotificationsEnabled = \App\Models\Setting::get('hiring_email_notifications', 'enabled');
        if ($emailNotificationsEnabled === 'enabled') {
            try {
                // Ensure mail configuration is up to date from settings
                MailConfigService::configure();

                Mail::to($application->email)
                    ->send(new \App\Mail\HiringApplicationStatusUpdate(
                        $application,
                        'rejected',
                        $request->admin_notes,
                        $application->hiringPosition
                    ));
            } catch (\Exception $e) {
                Log::error('Failed to send rejection email', [
                    'error' => $e->getMessage(),
                    'application_id' => $application->id
                ]);
            }
        }

        return redirect()->route('admin.hiring-applications.show', $application)
            ->with('success', 'Application rejected.');
    }

    public function scheduleInterview(Request $request, HiringApplication $application)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $application->update([
            'status' => 'interview_scheduled',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.hiring-applications.show', $application)
            ->with('success', 'Interview scheduled.');
    }

    public function downloadResume(HiringApplication $application)
    {
        if (!$application->resume_path) {
            abort(404, 'Resume not found.');
        }

        // Try digitalocean disk first
        try {
            if (Storage::disk('digitalocean')->exists($application->resume_path)) {
                return Storage::disk('digitalocean')->download(
                    $application->resume_path,
                    $application->full_name . '_resume.' . pathinfo($application->resume_path, PATHINFO_EXTENSION)
                );
            }
        } catch (\Throwable $e) {
            // Fallback to public disk
        }

        // Fallback to public disk
        if (Storage::disk('public')->exists($application->resume_path)) {
            return Storage::disk('public')->download(
                $application->resume_path,
                $application->full_name . '_resume.' . pathinfo($application->resume_path, PATHINFO_EXTENSION)
            );
        }

        abort(404, 'Resume not found.');
    }

    public function viewResume(HiringApplication $application)
    {
        if (!$application->resume_path) {
            abort(404, 'Resume not found.');
        }

        // Try digitalocean disk first
        try {
            if (Storage::disk('digitalocean')->exists($application->resume_path)) {
                $url = Storage::disk('digitalocean')->temporaryUrl(
                    $application->resume_path,
                    now()->addMinutes(30),
                    ['ResponseContentDisposition' => 'inline']
                );
                return redirect($url);
            }
        } catch (\Throwable $e) {
            // Fallback to public disk
        }

        // Fallback to public disk
        if (Storage::disk('public')->exists($application->resume_path)) {
            return Storage::disk('public')->response(
                $application->resume_path,
                null,
                ['Content-Disposition' => 'inline']
            );
        }

        abort(404, 'Resume not found.');
    }

    public function destroy(HiringApplication $application)
    {
        // Delete resume file if exists
        if ($application->resume_path && Storage::disk('public')->exists($application->resume_path)) {
            Storage::disk('public')->delete($application->resume_path);
        }

        $application->delete();

        return redirect()->route('admin.hiring-applications.index')
            ->with('success', 'Application deleted successfully.');
    }
}
