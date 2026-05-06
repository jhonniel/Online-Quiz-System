<?php

namespace App\Http\Controllers;

use App\Models\TeacherInviteLink;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeacherInviteController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.permission:user_management')->only(['adminIndex', 'adminStore']);
    }

    public function adminIndex()
    {
        $inviteLinks = TeacherInviteLink::query()
            ->with(['university', 'creator', 'usedByUser'])
            ->latest()
            ->paginate(15);

        $universities = University::active()->orderBy('name')->get();

        return view('admin/teachers/invite-links', compact('inviteLinks', 'universities'));
    }

    public function adminStore(Request $request)
    {
        $validated = $request->validate([
            'university_id' => ['nullable', 'exists:universities,id'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        $invite = TeacherInviteLink::create([
            'token' => Str::random(64),
            'university_id' => $validated['university_id'] ?? null,
            'department_id' => null,
            'created_by' => auth()->id(),
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => true,
        ]);

        $inviteUrl = url('/teacher/invite/'.$invite->token);

        return redirect(url('/admin/teachers-management/invite-links'))
            ->with('success', 'Teacher invite link generated.')
            ->with('generated_invite_url', $inviteUrl);
    }

    public function showActivationForm(string $token)
    {
        $invite = TeacherInviteLink::query()
            ->where('token', $token)
            ->first();

        if (! $invite || ! $invite->isUsable()) {
            return view('teacher-invites.expired');
        }

        return view('teacher-invites.activate', [
            'token' => $token,
            'invite' => $invite,
        ]);
    }

    public function activate(Request $request, string $token)
    {
        $invite = TeacherInviteLink::query()
            ->where('token', $token)
            ->first();

        if (! $invite || ! $invite->isUsable()) {
            return redirect(url('/teacher/invite/'.$token))
                ->withErrors(['invite' => 'This invitation link is no longer valid.']);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'teacher',
            'university_id' => $invite->university_id,
            'department_id' => null,
            'is_active' => true,
            'is_approved' => true,
        ]);

        $invite->update([
            'used_at' => now(),
            'used_by_user_id' => $user->id,
            'is_active' => false,
        ]);

        return redirect('/login')->with('success', 'Teacher account activated successfully. You can now log in.');
    }
}
