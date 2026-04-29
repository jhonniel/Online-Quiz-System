@extends('layouts.landing')

@section('title', 'Report a Problem')
@section('description', 'Submit a problem report')

@section('content')
<section class="gradient-bg text-white py-10 sm:py-16 md:py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 min-w-0">
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-14 h-14 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl bg-white/10 backdrop-blur-sm mb-4 sm:mb-6">
                <i class="fas fa-headset text-2xl sm:text-3xl text-white"></i>
            </div>
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-2 sm:mb-3 tracking-tight px-1">Report a Problem</h1>
            <p class="text-base sm:text-lg text-white/90 max-w-xl mx-auto px-0 sm:px-2">Describe the issue you’re experiencing. We’ll review it and get back to you using the contact details you provide.</p>
        </div>
    </div>
</section>

<section class="py-8 sm:py-12 md:py-16 bg-gray-50/80">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 min-w-0">
        <div class="mb-6 sm:mb-8 bg-white rounded-xl sm:rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100 overflow-hidden">
            <div class="p-4 sm:p-6 md:p-8 space-y-4">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-9 h-9 rounded-lg bg-primary/10 text-primary flex-shrink-0">
                        <i class="fas fa-ticket-alt text-sm"></i>
                    </span>
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900">Check ticket status</h2>
                </div>

                <form action="{{ url('/report-problem') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-[1fr_auto] gap-3">
                    <input
                        type="text"
                        name="ticket_number"
                        value="{{ old('ticket_number', $ticketLookupNumber ?? '') }}"
                        class="block w-full rounded-xl border border-gray-200 bg-gray-50/50 py-3 px-4 text-base sm:text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-colors min-h-[48px] touch-manipulation"
                        placeholder="Enter ticket number (e.g. TR-20260316-0001)"
                    >
                    <button type="submit" class="inline-flex items-center justify-center gap-2 py-3 px-6 rounded-xl text-white font-semibold bg-primary hover:opacity-95 active:opacity-90 focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all min-h-[48px] touch-manipulation text-base sm:text-sm">
                        <i class="fas fa-search text-sm"></i>
                        Check status
                    </button>
                </form>

                @if(!empty($ticketLookupError))
                    <div class="rounded-xl bg-red-50 border border-red-200/80 p-4 text-sm text-red-700">
                        {{ $ticketLookupError }}
                    </div>
                @endif

                @if(!empty($ticket))
                    @php
                        $paymentStatus = $ticket->payment_status ?? \App\Models\TicketReport::PAYMENT_STATUS_PENDING;
                        $paymentStatusLabel = $paymentStatus === \App\Models\TicketReport::PAYMENT_STATUS_PAID ? 'Paid' : 'Pending for payment';
                        $paymentBadge = $paymentStatus === \App\Models\TicketReport::PAYMENT_STATUS_PAID
                            ? 'bg-green-100 text-green-700'
                            : 'bg-amber-100 text-amber-700';
                    @endphp
                    <div class="rounded-xl border border-gray-200 bg-gray-50/70 p-4 sm:p-5 space-y-2">
                        <p class="text-sm text-gray-600">
                            <span class="font-semibold text-gray-900">Ticket Number:</span>
                            {{ $ticket->ticket_number }}
                        </p>
                        <p class="text-sm text-gray-600">
                            <span class="font-semibold text-gray-900">Payment Status:</span>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $paymentBadge }}">
                                {{ $paymentStatusLabel }}
                            </span>
                        </p>
                        <p class="text-sm text-gray-600">
                            <span class="font-semibold text-gray-900">Type:</span>
                            {{ $ticket->type_label }}
                        </p>
                        <p class="text-sm text-gray-600">
                            <span class="font-semibold text-gray-900">Submitted:</span>
                            {{ optional($ticket->created_at)->format('M d, Y h:i A') }}
                        </p>
                        @if($ticket->admin_attachment_url)
                            <p class="text-sm text-gray-600">
                                <span class="font-semibold text-gray-900">Admin Attachment:</span>
                                <a href="{{ $ticket->admin_attachment_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-indigo-600 hover:underline font-medium">
                                    View file
                                    <i class="fas fa-external-link-alt text-xs"></i>
                                </a>
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 sm:mb-8 rounded-xl bg-emerald-50 border border-emerald-200/80 p-4 sm:p-5 shadow-sm flex items-start gap-3 sm:gap-4">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                    <i class="fas fa-check text-emerald-600"></i>
                </div>
                <p class="text-emerald-800 font-medium text-sm sm:text-base break-words">{{ session('success') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 sm:mb-8 rounded-xl bg-red-50 border border-red-200/80 p-4 sm:p-5 shadow-sm">
                <div class="flex items-center gap-2 text-red-800 font-medium mb-2 text-sm sm:text-base">
                    <i class="fas fa-exclamation-circle flex-shrink-0"></i>
                    <span>Please correct the following:</span>
                </div>
                <ul class="list-disc list-inside space-y-1 text-sm text-red-700 break-words">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-xl sm:rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100 overflow-hidden">
            <form action="{{ url('/report-problem') }}" method="POST" enctype="multipart/form-data" class="divide-y divide-gray-100">
                @csrf

                {{-- Problem details --}}
                <div class="p-4 sm:p-6 md:p-8 space-y-5 sm:space-y-6">
                    <div class="flex items-center gap-3 mb-4 sm:mb-6">
                        <span class="flex items-center justify-center w-9 h-9 rounded-lg bg-primary/10 text-primary flex-shrink-0">
                            <i class="fas fa-clipboard-list text-sm"></i>
                        </span>
                        <h2 class="text-base sm:text-lg font-semibold text-gray-900">Problem details</h2>
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-semibold text-gray-700 mb-1.5">Type of problem <span class="text-red-500">*</span></label>
                        <select name="type" id="type" required class="block w-full rounded-xl border border-gray-200 bg-gray-50/50 py-3 px-4 text-base sm:text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-colors min-h-[48px] touch-manipulation">
                            <option value="">Select type...</option>
                            @foreach($problemTypes as $value => $label)
                                <option value="{{ $value }}" {{ old('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-semibold text-gray-700 mb-1.5">Describe the problem <span class="text-red-500">*</span></label>
                        <textarea name="description" id="description" rows="5" required class="block w-full rounded-xl border border-gray-200 bg-gray-50/50 py-3 px-4 text-base sm:text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-colors resize-y min-h-[120px] touch-manipulation" placeholder="What happened? What did you expect?">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Photo (optional)</label>
                        <label for="photo" class="mt-2 flex justify-center rounded-xl border-2 border-dashed border-gray-200 bg-gray-50/50 px-4 sm:px-6 py-6 sm:py-8 hover:border-gray-300 hover:bg-gray-50/80 active:bg-gray-50 transition-colors cursor-pointer min-h-[120px] sm:min-h-[140px]">
                            <div class="text-center pointer-events-none">
                                <i class="fas fa-cloud-upload-alt text-2xl sm:text-3xl text-gray-400 mb-2"></i>
                                <span class="block text-sm font-medium text-primary">Tap to choose file</span>
                                <p class="mt-1 text-xs text-gray-500">JPEG, PNG, GIF or WebP, max 5MB</p>
                            </div>
                            <input type="file" name="photo" id="photo" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="sr-only">
                        </label>
                    </div>
                </div>

                {{-- Your information --}}
                <div class="p-4 sm:p-6 md:p-8 space-y-5 sm:space-y-6">
                    <div class="flex items-center gap-3 mb-4 sm:mb-6">
                        <span class="flex items-center justify-center w-9 h-9 rounded-lg bg-primary/10 text-primary flex-shrink-0">
                            <i class="fas fa-user text-sm"></i>
                        </span>
                        <h2 class="text-base sm:text-lg font-semibold text-gray-900">Your information</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 sm:gap-6">
                        <div class="sm:col-span-2">
                            <label for="full_name" class="block text-sm font-semibold text-gray-700 mb-1.5">Full name <span class="text-red-500">*</span></label>
                            <input type="text" name="full_name" id="full_name" value="{{ old('full_name') }}" required class="block w-full rounded-xl border border-gray-200 bg-gray-50/50 py-3 px-4 text-base sm:text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-colors min-h-[48px] touch-manipulation" placeholder="Your full name" autocomplete="name">
                        </div>
                        <div>
                            <label for="contact_number" class="block text-sm font-semibold text-gray-700 mb-1.5">Contact number</label>
                            <input type="tel" name="contact_number" id="contact_number" value="{{ old('contact_number') }}" class="block w-full rounded-xl border border-gray-200 bg-gray-50/50 py-3 px-4 text-base sm:text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-colors min-h-[48px] touch-manipulation" placeholder="Phone number" autocomplete="tel">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required class="block w-full rounded-xl border border-gray-200 bg-gray-50/50 py-3 px-4 text-base sm:text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-colors min-h-[48px] touch-manipulation" placeholder="you@example.com" autocomplete="email">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="office" class="block text-sm font-semibold text-gray-700 mb-1.5">Office</label>
                            <input type="text" name="office" id="office" value="{{ old('office') }}" class="block w-full rounded-xl border border-gray-200 bg-gray-50/50 py-3 px-4 text-base sm:text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-colors min-h-[48px] touch-manipulation" placeholder="Department or office">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="address" class="block text-sm font-semibold text-gray-700 mb-1.5">Address</label>
                            <textarea name="address" id="address" rows="2" class="block w-full rounded-xl border border-gray-200 bg-gray-50/50 py-3 px-4 text-base sm:text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-colors resize-none touch-manipulation" placeholder="Street, city, country" autocomplete="street-address">{{ old('address') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="p-4 sm:p-6 md:p-8 bg-gray-50/50">
                    <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 py-3.5 px-6 sm:px-8 rounded-xl text-white font-semibold shadow-lg shadow-gray-900/10 bg-primary hover:opacity-95 active:opacity-90 focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all min-h-[48px] touch-manipulation text-base sm:text-sm">
                        <i class="fas fa-paper-plane text-sm"></i>
                        Submit report
                    </button>
                    <p class="mt-3 sm:mt-4 text-xs text-gray-500 break-words">We’ll send a confirmation to your email and use it to follow up if needed.</p>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
