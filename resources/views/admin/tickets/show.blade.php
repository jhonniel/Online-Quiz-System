@extends('layouts.admin')

@section('title', 'Ticket ' . $ticket->ticket_number)

@section('content')
<div class="px-3 sm:px-4 lg:px-6 xl:px-8 w-full">
    <div class="flex flex-col lg:flex-row lg:gap-8">
        {{-- Main content --}}
        <div class="flex-1 min-w-0 max-w-none">
            {{-- Breadcrumb & header --}}
            <div class="mb-6">
                <a href="{{ url('admin/tickets') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Tickets
                </a>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight mt-4">Ticket {{ $ticket->ticket_number }}</h1>
            </div>

            @if(session('success'))
                <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            @endif

            <div class="space-y-6">
        {{-- Report details --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex items-center gap-2">
                    <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Report details</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Submitted by reporter</p>
                    </div>
                </div>
            </div>
            <div class="p-6 space-y-8">
                {{-- Header metrics --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="rounded-xl border border-indigo-100 bg-indigo-50/60 p-4">
                        <p class="text-xs font-semibold text-indigo-700 uppercase tracking-wide">Category</p>
                        <p class="mt-1.5 text-sm font-semibold text-indigo-900">{{ $ticket->type_label }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Submitted</p>
                        <p class="mt-1.5 text-sm font-semibold text-gray-900">{{ $ticket->created_at->format('M j, Y') }}</p>
                        <p class="text-xs text-gray-500">{{ $ticket->created_at->format('g:i A') }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Assigned To</p>
                        <p class="mt-1.5 text-sm font-semibold text-gray-900">{{ $ticket->assignedTo?->name ?? 'Unassigned' }}</p>
                        @if($ticket->assignedTo)
                            <p class="text-xs text-gray-500">{{ $ticket->assignedTo->email }}</p>
                        @endif
                    </div>
                </div>

                {{-- Description --}}
                <div>
                    <div class="flex items-center justify-between gap-3 mb-2">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Description</p>
                        <span class="text-xs text-gray-400">Ticket #{{ $ticket->ticket_number }}</span>
                    </div>
                    <div class="relative rounded-xl border border-gray-200 bg-gradient-to-br from-gray-50 to-white overflow-hidden">
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-indigo-500"></div>
                        <div class="py-5 px-5 pl-6 text-gray-800 whitespace-pre-wrap text-sm leading-relaxed min-h-[120px]">{{ $ticket->description }}</div>
                    </div>
                </div>

                @if($ticket->image_url)
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Attached photo</p>
                        <div class="rounded-xl border border-gray-200 overflow-hidden bg-gray-50 inline-block">
                            <a href="{{ $ticket->image_url }}" target="_blank" rel="noopener" class="block group">
                                <img src="{{ $ticket->image_url }}" alt="Attachment" class="max-h-72 w-auto object-contain group-hover:opacity-95 transition-opacity">
                                <div class="px-3 py-2 bg-white border-t border-gray-100 text-xs text-indigo-600 font-medium flex items-center gap-1.5 group-hover:underline">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    Open in new tab
                                </div>
                            </a>
                        </div>
                    </div>
                @endif

                @if($ticket->admin_attachment_url)
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Admin attachment</p>
                        <a href="{{ $ticket->admin_attachment_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.828a4 4 0 10-5.657-5.657L5.757 10.757a6 6 0 108.486 8.486L20.5 13"></path></svg>
                            View uploaded file
                        </a>
                    </div>
                @endif

                {{-- Contact information --}}
                <div class="pt-2">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-4">Contact information</p>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                        <div class="flex gap-3 p-4 rounded-lg bg-gray-50/80 border border-gray-100">
                            <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <dt class="text-xs font-medium text-gray-500">Full name</dt>
                                <dd class="mt-0.5 text-sm font-medium text-gray-900 truncate">{{ $ticket->full_name }}</dd>
                            </div>
                        </div>
                        <div class="flex gap-3 p-4 rounded-lg bg-gray-50/80 border border-gray-100">
                            <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <dt class="text-xs font-medium text-gray-500">Email</dt>
                                <dd class="mt-0.5 text-sm font-medium truncate"><a href="mailto:{{ $ticket->email }}" class="text-indigo-600 hover:underline">{{ $ticket->email }}</a></dd>
                            </div>
                        </div>
                        @if($ticket->contact_number)
                            <div class="flex gap-3 p-4 rounded-lg bg-gray-50/80 border border-gray-100">
                                <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                </div>
                                <div class="min-w-0">
                                    <dt class="text-xs font-medium text-gray-500">Contact number</dt>
                                    <dd class="mt-0.5 text-sm font-medium text-gray-900">{{ $ticket->contact_number }}</dd>
                                </div>
                            </div>
                        @endif
                        @if($ticket->office)
                            <div class="flex gap-3 p-4 rounded-lg bg-gray-50/80 border border-gray-100">
                                <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                </div>
                                <div class="min-w-0">
                                    <dt class="text-xs font-medium text-gray-500">Office</dt>
                                    <dd class="mt-0.5 text-sm font-medium text-gray-900">{{ $ticket->office }}</dd>
                                </div>
                            </div>
                        @endif
                        @if($ticket->address)
                            <div class="flex gap-3 p-4 rounded-lg bg-gray-50/80 border border-gray-100 sm:col-span-2 xl:col-span-3">
                                <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </div>
                                <div class="min-w-0">
                                    <dt class="text-xs font-medium text-gray-500">Address</dt>
                                    <dd class="mt-0.5 text-sm font-medium text-gray-900">{{ $ticket->address }}</dd>
                                </div>
                            </div>
                        @endif
                    </dl>
                </div>

                {{-- Timestamps --}}
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 pt-4 border-t border-gray-200 text-sm text-gray-500">
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Submitted {{ $ticket->created_at->format('F j, Y \a\t g:i A') }}
                    </span>
                    @if($ticket->updated_at != $ticket->created_at)
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            Last updated {{ $ticket->updated_at->format('F j, Y \a\t g:i A') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Admin notes --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/80">
                <h2 class="text-base font-semibold text-gray-900">Admin comments</h2>
                <p class="text-sm text-gray-500 mt-0.5">Internal comments. Only visible to admins. Only the author can delete their own comment.</p>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @if($ticket->notes->count() === 0)
                        <p class="text-sm text-gray-500">No comments yet.</p>
                    @else
                        <ul class="space-y-3">
                            @foreach($ticket->notes as $note)
                                <li class="rounded-xl border border-gray-200 bg-gray-50/60 p-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ $note->body }}</p>
                                            <p class="mt-2 text-xs text-gray-500">
                                                {{ $note->user?->name ?? 'Unknown' }} · {{ $note->created_at->format('M j, Y g:i A') }}
                                            </p>
                                        </div>
                                        @if(auth()->id() === $note->user_id)
                                            <form method="POST" action="{{ url('admin/tickets/' . $ticket->id . '/notes/' . $note->id) }}" onsubmit="return confirm('Delete your comment?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center px-3 py-2 rounded-lg text-xs font-semibold border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 transition-colors">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <form method="POST" action="{{ url('admin/tickets/' . $ticket->id . '/notes') }}" class="pt-2">
                        @csrf
                        <label for="note_body" class="block text-sm font-semibold text-gray-700 mb-1.5">Add comment</label>
                        <textarea name="body" id="note_body" rows="4" class="block w-full rounded-lg border border-gray-300 shadow-sm py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm placeholder-gray-400" placeholder="Write an internal comment…">{{ old('body') }}</textarea>
                        <div class="mt-3 flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2.5 border border-transparent rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors shadow-sm">
                                Post comment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
            </div>
        </div>

        {{-- Right sidebar --}}
        <aside class="lg:w-96 xl:w-[28rem] flex-shrink-0 mt-8 lg:mt-0">
            <div class="lg:sticky lg:top-6 space-y-6">
                {{-- Status & summary --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-sm font-semibold text-gray-900">Summary</h3>
                    </div>
                    <div class="p-4 space-y-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Status</p>
                            <span class="inline-flex items-center mt-1 px-3 py-1 rounded-full text-sm font-medium {{ $ticket->isOpen() ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800' }}">
                                {{ $ticket->status }}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Status</p>
                            @php
                                $isPaid = ($ticket->payment_status ?? \App\Models\TicketReport::PAYMENT_STATUS_PENDING) === \App\Models\TicketReport::PAYMENT_STATUS_PAID;
                            @endphp
                            <span class="inline-flex items-center mt-1 px-3 py-1 rounded-full text-sm font-medium {{ $isPaid ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $isPaid ? 'Paid' : 'Pending for payment' }}
                            </span>
                            <p class="mt-1 text-sm text-gray-700">
                                Amount:
                                @if($ticket->amount_paid !== null)
                                    &#8369;{{ number_format((float) $ticket->amount_paid, 2) }}
                                @else
                                    —
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Category</p>
                            <p class="mt-1 text-sm font-medium text-gray-900">{{ $ticket->type_label }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</p>
                            <p class="mt-1 text-sm text-gray-700">{{ $ticket->created_at->format('M j, Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $ticket->created_at->format('g:i A') }}</p>
                        </div>
                        @if($ticket->updated_at != $ticket->created_at)
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Last updated</p>
                                <p class="mt-1 text-sm text-gray-700">{{ $ticket->updated_at->format('M j, Y g:i A') }}</p>
                            </div>
                        @endif
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned to</p>
                            <p class="mt-1 text-sm text-gray-700">{{ $ticket->assignedTo?->name ?? 'Unassigned' }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-sm font-semibold text-gray-900">Payment</h3>
                    </div>
                    <div class="p-4">
                        <form method="POST" action="{{ url('admin/tickets/' . $ticket->id) }}" class="space-y-3">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="{{ $ticket->status }}">
                            <label for="payment_status" class="block text-xs font-medium text-gray-600">Payment status</label>
                            <select name="payment_status" id="payment_status" class="block w-full rounded-lg border border-gray-300 py-2.5 px-3 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="{{ \App\Models\TicketReport::PAYMENT_STATUS_PENDING }}" {{ ($ticket->payment_status ?? \App\Models\TicketReport::PAYMENT_STATUS_PENDING) === \App\Models\TicketReport::PAYMENT_STATUS_PENDING ? 'selected' : '' }}>
                                    Pending for payment
                                </option>
                                <option value="{{ \App\Models\TicketReport::PAYMENT_STATUS_PAID }}" {{ ($ticket->payment_status ?? \App\Models\TicketReport::PAYMENT_STATUS_PENDING) === \App\Models\TicketReport::PAYMENT_STATUS_PAID ? 'selected' : '' }}>
                                    Paid
                                </option>
                            </select>
                            <label for="amount_paid" class="block text-xs font-medium text-gray-600">Amount paid</label>
                            <input
                                type="number"
                                name="amount_paid"
                                id="amount_paid"
                                min="0"
                                step="0.01"
                                value="{{ old('amount_paid', $ticket->amount_paid !== null ? number_format((float) $ticket->amount_paid, 2, '.', '') : '') }}"
                                placeholder="0.00"
                                class="block w-full rounded-lg border border-gray-300 py-2.5 px-3 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                            >
                            <p class="text-xs text-gray-500">This amount is admin-only and hidden from users.</p>
                            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 border border-transparent rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors shadow-sm">
                                Save payment details
                            </button>
                        </form>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-sm font-semibold text-gray-900">Assign Ticket</h3>
                    </div>
                    <div class="p-4">
                        <form method="POST" action="{{ url('admin/tickets/' . $ticket->id) }}" class="space-y-3">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="{{ $ticket->status }}">
                            <label for="assigned_to_user_id" class="block text-xs font-medium text-gray-600">Assign to technician</label>
                            <select name="assigned_to_user_id" id="assigned_to_user_id" class="block w-full rounded-lg border border-gray-300 py-2.5 px-3 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Unassigned</option>
                                @foreach($assignees as $assignee)
                                    <option value="{{ $assignee->id }}" {{ (int) ($ticket->assigned_to_user_id ?? 0) === (int) $assignee->id ? 'selected' : '' }}>
                                        {{ $assignee->name }} ({{ $assignee->email }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500">Only users with role <span class="font-semibold">technician</span> are shown.</p>
                            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 border border-transparent rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors shadow-sm">
                                Save assignee
                            </button>
                        </form>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-sm font-semibold text-gray-900">Upload File for Reporter</h3>
                    </div>
                    <div class="p-4">
                        <form method="POST" action="{{ url('admin/tickets/' . $ticket->id) }}" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            @method('PATCH')
                            <label for="admin_attachment" class="block text-xs font-medium text-gray-600">Attachment file</label>
                            <input type="file" name="admin_attachment" id="admin_attachment" class="block w-full rounded-lg border border-gray-300 py-2 px-3 text-sm text-gray-700 focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="text-xs text-gray-500">Allowed: PDF, Office docs, images, TXT, ZIP/RAR (max 10MB).</p>
                            @if($ticket->admin_attachment_url)
                                <a href="{{ $ticket->admin_attachment_url }}" target="_blank" rel="noopener" class="inline-flex items-center text-xs font-medium text-indigo-600 hover:underline">Current file: Open attachment</a>
                            @endif
                            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 border border-transparent rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors shadow-sm">
                                Upload attachment
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Quick actions --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-sm font-semibold text-gray-900">Actions</h3>
                    </div>
                    <div class="p-4">
                        <div class="space-y-2">
                            @if($ticket->status === \App\Models\TicketReport::STATUS_OPEN)
                                <form method="POST" action="{{ url('admin/tickets/' . $ticket->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ \App\Models\TicketReport::STATUS_PROCESSING }}">
                                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 border border-transparent rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors shadow-sm">
                                        Click to process
                                    </button>
                                </form>
                            @elseif($ticket->status === \App\Models\TicketReport::STATUS_PROCESSING)
                                <form method="POST" action="{{ url('admin/tickets/' . $ticket->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ \App\Models\TicketReport::STATUS_RESOLVED }}">
                                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 border border-transparent rounded-lg text-sm font-medium text-white bg-green-600 hover:bg-green-700 transition-colors shadow-sm">
                                        Mark as resolved
                                    </button>
                                </form>
                                <form method="POST" action="{{ url('admin/tickets/' . $ticket->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ \App\Models\TicketReport::STATUS_NEEDS_INVESTIGATION }}">
                                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 border border-purple-200 rounded-lg text-sm font-medium text-purple-800 bg-purple-50 hover:bg-purple-100 transition-colors">
                                        Need further investigation
                                    </button>
                                </form>
                            @elseif($ticket->status === \App\Models\TicketReport::STATUS_NEEDS_INVESTIGATION)
                                <form method="POST" action="{{ url('admin/tickets/' . $ticket->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ \App\Models\TicketReport::STATUS_RESOLVED }}">
                                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 border border-transparent rounded-lg text-sm font-medium text-white bg-green-600 hover:bg-green-700 transition-colors shadow-sm">
                                        Mark as resolved
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ url('admin/tickets/' . $ticket->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ \App\Models\TicketReport::STATUS_OPEN }}">
                                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                        Reopen ticket
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Logs --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-sm font-semibold text-gray-900">Logs</h3>
                    </div>
                    <div class="p-4">
                        @if($ticket->logs->count() === 0)
                            <p class="text-sm text-gray-500">No activity yet.</p>
                        @else
                            <ul class="space-y-2">
                                @foreach($ticket->logs as $log)
                                    <li class="text-xs text-gray-600">
                                        <span class="font-semibold text-gray-900">{{ $log->user?->name ?? 'Unknown' }}</span>
                                        {{ str_replace('_', ' ', $log->action) }}
                                        @if($log->from_status !== null && $log->to_status !== null && $log->from_status !== $log->to_status)
                                            ({{ $log->from_status }} → {{ $log->to_status }})
                                        @endif
                                        · {{ $log->created_at->diffForHumans() }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                {{-- Reporter --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-sm font-semibold text-gray-900">Reported by</h3>
                    </div>
                    <div class="p-4 space-y-2">
                        <p class="text-sm font-medium text-gray-900">{{ $ticket->full_name }}</p>
                        <a href="mailto:{{ $ticket->email }}" class="text-sm text-indigo-600 hover:underline block truncate">{{ $ticket->email }}</a>
                        @if($ticket->contact_number)
                            <p class="text-sm text-gray-600">{{ $ticket->contact_number }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
