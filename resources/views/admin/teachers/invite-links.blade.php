@extends('layouts.admin')

@section('page-title', 'Teacher Invite Links')

@section('content')
<div class="space-y-4 px-3 sm:px-4 lg:px-6">
    <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5">
        <h1 class="text-lg sm:text-xl font-semibold text-gray-900">Teacher Invite Links</h1>
        <p class="text-sm text-gray-600 mt-1">Generate a link for teacher account activation. Invitees will set their own name, email, contact number, and password.</p>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5">
        <h2 class="text-base font-semibold text-gray-900 mb-4">Generate New Link</h2>
        <form method="POST" action="{{ url('/admin/teachers-management/invite-links') }}" class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @csrf
            <div>
                <label for="university_id" class="block text-sm font-medium text-gray-700 mb-1">School (optional)</label>
                <select id="university_id" name="university_id" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">Any school</option>
                    @foreach($universities as $university)
                        <option value="{{ $university->id }}" @selected((string) old('university_id') === (string) $university->id)>{{ $university->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-1">Expires At (optional)</label>
                <input id="expires_at" type="datetime-local" name="expires_at" value="{{ old('expires_at') }}" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
                    Generate Invite Link
                </button>
            </div>
        </form>
    </div>

    @if(session('generated_invite_url'))
        <div class="bg-green-50 border border-green-200 rounded-xl p-4">
            <p class="text-sm font-medium text-green-800 mb-2">Generated Link</p>
            <div class="flex flex-col sm:flex-row gap-2">
                <input type="text" readonly value="{{ session('generated_invite_url') }}" class="w-full rounded-lg border-green-300 text-sm bg-white">
                <button type="button" data-url="{{ session('generated_invite_url') }}" onclick="navigator.clipboard.writeText(this.dataset.url)" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-medium hover:bg-green-700">
                    Copy
                </button>
            </div>
        </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 sm:px-5 py-3 border-b border-gray-200">
            <h2 class="text-base font-semibold text-gray-900">Generated Links</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Link</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">School</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact Number</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($inviteLinks as $invite)
                        @php
                            $status = $invite->isUsable() ? 'Active' : ($invite->used_at ? 'Used' : 'Expired/Disabled');
                            $statusClass = $invite->isUsable()
                                ? 'bg-green-100 text-green-800'
                                : ($invite->used_at ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700');
                            $inviteUrl = url('/teacher/invite/'.$invite->token);
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <div class="flex items-center gap-2">
                                    <a href="{{ $inviteUrl }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 break-all">{{ $inviteUrl }}</a>
                                    <button type="button" data-url="{{ $inviteUrl }}" onclick="navigator.clipboard.writeText(this.dataset.url)" class="text-xs px-2 py-1 rounded bg-gray-100 hover:bg-gray-200">Copy</button>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ optional($invite->university)->name ?? 'Any school' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ optional($invite->usedByUser)->contact_number ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $statusClass }}">{{ $status }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ optional($invite->creator)->name ?? 'System' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                <div>{{ optional($invite->created_at)->format('M d, Y h:i A') }}</div>
                                @if($invite->expires_at)
                                    <div class="text-xs">Expires: {{ $invite->expires_at->format('M d, Y h:i A') }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No invite links yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 sm:px-5 py-3 border-t border-gray-200">
            {{ $inviteLinks->links() }}
        </div>
    </div>
</div>
@endsection

