@extends('layouts.say-it')

@section('title', $room->name.' – Anonymous Chat')
@section('main_class', 'p-0 overflow-hidden flex flex-col')
@section('hide_flash')
@endsection

@push('styles')
<style>
    #say-it-chat-app {
        display: flex;
        flex-direction: column;
        height: 100%;
        min-height: 0;
        flex: 1 1 auto;
        width: 100%;
        background:
            radial-gradient(ellipse at top, rgba(124, 58, 237, 0.05), transparent 45%),
            #f3f4f6;
    }
    #say-it-chat-panel {
        display: flex;
        flex-direction: column;
        height: 100%;
        min-height: 0;
        width: 100%;
        max-width: 48rem;
        margin: 0 auto;
        flex: 1 1 auto;
        background: #fff;
        border-left: 1px solid #e5e7eb;
        border-right: 1px solid #e5e7eb;
    }
    @media (max-width: 639px) {
        #say-it-chat-panel {
            border-left: 0;
            border-right: 0;
            max-width: none;
        }
    }
    #say-it-chat-messages {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
        scrollbar-width: thin;
        scrollbar-color: #d1d5db transparent;
        display: flex;
        flex-direction: column;
    }
    #say-it-chat-messages::-webkit-scrollbar { width: 6px; }
    #say-it-chat-messages::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 999px;
    }
    #say-it-chat-empty:not(.hidden) {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 100%;
    }
    .say-it-msg-row { display: flex; gap: 0.75rem; max-width: 100%; }
    .say-it-msg-row.is-own { flex-direction: row-reverse; }
    .say-it-msg-avatar {
        flex-shrink: 0;
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        margin-top: 0.15rem;
    }
    .say-it-msg-stack { max-width: min(78%, 26rem); min-width: 0; }
    .say-it-msg-row.is-own .say-it-msg-stack { display: flex; flex-direction: column; align-items: flex-end; }
    .say-it-msg-meta {
        display: flex;
        align-items: baseline;
        gap: 0.5rem;
        margin-bottom: 0.2rem;
        padding: 0 0.15rem;
    }
    .say-it-msg-row.is-own .say-it-msg-meta { flex-direction: row-reverse; }
    .say-it-msg-bubble {
        border-radius: 1.1rem;
        padding: 0.65rem 1rem;
        font-size: 0.9375rem;
        line-height: 1.5;
        word-break: break-word;
        white-space: pre-wrap;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }
    .say-it-msg-bubble.is-own {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        color: #fff;
        border-bottom-right-radius: 0.35rem;
    }
    .say-it-msg-bubble.is-other {
        background: #f9fafb;
        color: #111827;
        border: 1px solid #e5e7eb;
        border-bottom-left-radius: 0.35rem;
    }
    .say-it-msg-bubble.has-image-only {
        padding: 0.35rem;
        background: transparent;
        border: 0;
        box-shadow: none;
    }
    .say-it-msg-bubble.is-own.has-image-only {
        background: transparent;
    }
    .say-it-msg-image {
        display: block;
        max-width: 100%;
        max-height: 16rem;
        border-radius: 0.85rem;
        object-fit: cover;
    }
    .say-it-msg-image-wrap {
        position: relative;
        overflow: hidden;
        border-radius: 0.85rem;
    }
    .say-it-msg-image-meta {
        margin-top: 0.35rem;
        font-size: 10px;
        opacity: 0.75;
    }
    .say-it-composer {
        border-top: 1px solid #e5e7eb;
        background: rgba(255,255,255,0.98);
        backdrop-filter: blur(8px);
    }
    .say-it-composer-inner {
        width: 100%;
        max-width: none;
    }
    #say-it-chat-input,
    #say-it-chat-input:focus,
    #say-it-chat-input:focus-visible {
        border: 0 !important;
        outline: none !important;
        box-shadow: none !important;
        --tw-ring-shadow: 0 0 #0000 !important;
        --tw-ring-offset-shadow: 0 0 #0000 !important;
    }
    .say-it-image-preview {
        display: none;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.65rem;
        padding: 0.5rem 0.65rem;
        border-radius: 0.85rem;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
    }
    .say-it-image-preview.is-visible { display: flex; }
    .say-it-image-preview img {
        height: 3.25rem;
        width: 3.25rem;
        object-fit: cover;
        border-radius: 0.65rem;
    }
</style>
@endpush

@section('content')
<div id="say-it-chat-app">
    <div id="say-it-chat-panel">
        @if(session('success'))
            <div class="flex-shrink-0 mx-4 sm:mx-6 lg:mx-8 mt-3 py-2.5 px-3 rounded-xl bg-emerald-50 text-emerald-800 text-xs sm:text-sm font-medium border border-emerald-200">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="flex-shrink-0 mx-4 sm:mx-6 lg:mx-8 mt-3 py-2.5 px-3 rounded-xl bg-red-50 text-red-800 text-xs sm:text-sm font-medium border border-red-200">{{ session('error') }}</div>
        @endif

        {{-- Header --}}
        <header class="flex-shrink-0 border-b border-gray-200 bg-white px-4 sm:px-6 lg:px-8 py-3.5 flex items-center gap-3">
            <a href="{{ route('say-it.chat.index') }}"
               class="flex h-9 w-9 items-center justify-center rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-800 transition"
               aria-label="Back to rooms">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>

            @if($isRoomOwner)
                <form id="say-it-room-avatar-form" method="POST" action="{{ route('say-it.chat.avatar', $room) }}" enctype="multipart/form-data" class="shrink-0">
                    @csrf
                    <label for="say-it-room-avatar-input" class="relative block cursor-pointer group" title="Change room profile photo">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl shadow-sm overflow-hidden ring-2 ring-transparent group-hover:ring-violet-300 transition {{ $room->avatar_url ? 'bg-gradient-to-br from-violet-500 to-indigo-600 text-white' : \App\Helpers\SayItHelper::roomPlaceholderColorClasses($room->slug) }}">
                            @if($room->avatar_url)
                                <img src="{{ $room->avatar_url }}" alt="" class="h-full w-full object-cover">
                            @else
                                <i class="{{ \App\Helpers\SayItHelper::roomPlaceholderIcon($room->slug) }} text-sm"></i>
                            @endif
                        </span>
                        <span class="absolute -bottom-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-white text-violet-700 text-[9px] shadow border border-gray-200">
                            <i class="fas fa-camera"></i>
                        </span>
                        <input id="say-it-room-avatar-input"
                               type="file"
                               name="avatar"
                               accept="image/jpeg,image/png,image/gif,image/webp"
                               class="sr-only"
                               onchange="this.form.submit()">
                    </label>
                </form>
            @else
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl shadow-sm overflow-hidden {{ $room->avatar_url ? 'bg-gradient-to-br from-violet-500 to-indigo-600 text-white' : \App\Helpers\SayItHelper::roomPlaceholderColorClasses($room->slug) }}">
                    @if($room->avatar_url)
                        <img src="{{ $room->avatar_url }}" alt="" class="h-full w-full object-cover">
                    @else
                        <i class="{{ \App\Helpers\SayItHelper::roomPlaceholderIcon($room->slug) }} text-sm"></i>
                    @endif
                </div>
            @endif

            <div class="min-w-0 flex-1">
                <h1 class="text-sm sm:text-base font-bold text-gray-900 truncate leading-tight">{{ $room->name }}</h1>
                <p class="text-[11px] sm:text-xs text-gray-500 truncate mt-0.5">
                    Anonymous room · chatting as
                    <span class="font-semibold text-violet-700">{{ $codename }}</span>
                    @if($room->hasPassword())
                        <span class="text-gray-400">·</span>
                        <span class="text-gray-600"><i class="fas fa-lock text-[9px]"></i> Locked</span>
                    @endif
                    @if($isRoomOwner)
                        <span class="text-gray-400">·</span>
                        <span class="text-violet-600">tap photo to update</span>
                    @endif
                </p>
                @if($isRoomOwner && !empty($roomPassword))
                    <details class="mt-1.5 group/pw">
                        <summary class="cursor-pointer list-none text-[11px] font-semibold text-slate-600 hover:text-violet-700 inline-flex items-center gap-1">
                            <i class="fas fa-key text-[10px]"></i>
                            View room password
                        </summary>
                        <p class="mt-1 font-mono text-xs font-bold text-gray-900 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 select-all inline-block">{{ $roomPassword }}</p>
                    </details>
                @endif
                @error('avatar')
                    <p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>
                @enderror
            </div>
            <div class="hidden sm:flex shrink-0 items-center gap-2 rounded-full bg-gray-50 ring-1 ring-gray-200 px-2.5 py-1.5">
                <span class="flex h-6 w-6 items-center justify-center rounded-full text-[10px] {{ \App\Helpers\SayItHelper::avatarColorClassesForCodename($codename) }}">
                    <i class="{{ \App\Helpers\SayItHelper::animalIconForCodename($codename) }}"></i>
                </span>
                <span class="text-[11px] font-medium text-gray-600 max-w-[7rem] truncate">{{ $codename }}</span>
            </div>
        </header>

        <div id="say-it-room-status" class="flex-shrink-0 px-4 sm:px-6 lg:px-8 {{ ($roomStatus['is_frozen'] ?? false) || ($roomStatus['gibberish_active'] ?? false) ? 'pt-3' : '' }}">
            <div id="say-it-status-frozen" class="{{ ($roomStatus['is_frozen'] ?? false) ? '' : 'hidden' }} mb-2 py-2.5 px-3 rounded-xl bg-amber-50 text-amber-900 text-xs sm:text-sm font-medium border border-amber-200">
                This room is frozen. Sending new messages is disabled.
            </div>
            <div id="say-it-status-gibberish" class="{{ ($roomStatus['gibberish_active'] ?? false) ? '' : 'hidden' }} mb-2 py-2.5 px-3 rounded-xl bg-fuchsia-50 text-fuchsia-900 text-xs sm:text-sm font-medium border border-fuchsia-200">
                Gibberish mode is active — messages look scrambled for 1 hour.
            </div>
        </div>

        {{-- Messages --}}
        <div id="say-it-chat-messages" class="px-4 sm:px-6 lg:px-8 py-5 space-y-4">
            <div id="say-it-chat-empty" class="hidden py-10 px-6 text-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-50 text-violet-600 mb-4 ring-1 ring-violet-100">
                    <i class="fas fa-comment-dots text-xl"></i>
                </div>
                <p class="text-sm font-semibold text-gray-900">Start the conversation</p>
                <p class="mt-1 text-sm text-gray-500 max-w-sm">Messages are anonymous. Images auto-delete after 1 hour.</p>
            </div>
        </div>

        {{-- Composer --}}
        <form id="say-it-chat-form" class="say-it-composer flex-shrink-0 px-4 sm:px-6 lg:px-8 py-3 sm:py-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] {{ ($roomStatus['is_frozen'] ?? false) ? 'opacity-60 pointer-events-none' : '' }}">
            <div id="say-it-image-preview" class="say-it-image-preview">
                <img id="say-it-image-preview-img" src="" alt="">
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-medium text-gray-800 truncate" id="say-it-image-preview-name">Image</p>
                    <p class="text-[10px] text-gray-500">Deletes automatically after 1 hour</p>
                </div>
                <button type="button" id="say-it-image-preview-clear" class="text-xs font-semibold text-gray-500 hover:text-red-600 transition px-2 py-1">Remove</button>
            </div>
            <div class="say-it-composer-inner flex items-end gap-2 rounded-2xl border border-gray-200 bg-gray-50 focus-within:bg-white focus-within:ring-2 focus-within:ring-violet-500/30 focus-within:border-violet-400 transition px-2.5 py-2.5">
                <label for="say-it-chat-image" class="inline-flex items-center justify-center h-10 w-10 rounded-xl text-gray-500 hover:bg-violet-50 hover:text-violet-700 transition cursor-pointer shrink-0" title="Attach image" aria-label="Attach image">
                    <i class="fas fa-image text-sm"></i>
                    <input id="say-it-chat-image" type="file" accept="image/jpeg,image/png,image/gif,image/webp" class="sr-only" @if($roomStatus['is_frozen'] ?? false) disabled @endif>
                </label>
                <textarea id="say-it-chat-input"
                          rows="1"
                          maxlength="2000"
                          placeholder="{{ ($roomStatus['is_frozen'] ?? false) ? 'Room is frozen…' : 'Write a message…' }}"
                          class="flex-1 min-w-0 resize-none border-0 bg-transparent px-1.5 py-1.5 text-sm sm:text-[0.9375rem] text-gray-900 placeholder:text-gray-400 shadow-none outline-none ring-0 focus:border-0 focus:outline-none focus:ring-0 focus:shadow-none max-h-28 leading-5"
                          @if($roomStatus['is_frozen'] ?? false) disabled @endif></textarea>
                <button type="submit"
                        id="say-it-chat-send"
                        class="inline-flex items-center justify-center h-10 w-10 rounded-xl bg-violet-600 text-white hover:bg-violet-700 disabled:opacity-40 disabled:cursor-not-allowed transition shadow-sm shrink-0"
                        aria-label="Send message"
                        @if($roomStatus['is_frozen'] ?? false) disabled @endif>
                    <i class="fas fa-paper-plane text-xs"></i>
                </button>
            </div>
            <div class="mt-1.5 flex items-center justify-between gap-2 px-1">
                <p class="text-[10px] text-gray-400">Enter to send · Images expire after 1 hour</p>
                <p id="say-it-chat-error" class="hidden text-[11px] text-red-600 font-medium"></p>
            </div>
        </form>
    </div>
</div>

@if(!empty($moderationCodes) && is_array($moderationCodes))
<div id="say-it-codes-modal" class="fixed inset-0 z-[80] flex items-center justify-center p-4 bg-black/50" role="dialog" aria-modal="true" aria-labelledby="say-it-codes-title">
    <div class="w-full max-w-md rounded-2xl bg-white shadow-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-violet-50">
            <h2 id="say-it-codes-title" class="text-base font-bold text-gray-900">Your one-time room codes</h2>
            <p class="mt-1 text-xs text-gray-600">Shown once only. Save them now. Typing a code in chat triggers its effect.</p>
        </div>
        <div class="px-5 py-4 space-y-3">
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2.5">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-800">Freeze room</p>
                <p class="mt-1 font-mono text-sm font-bold text-gray-900 select-all">{{ $moderationCodes['freeze'] ?? '' }}</p>
                <p class="mt-0.5 text-[11px] text-amber-800/80">Locks the chat so nobody can send new messages.</p>
            </div>
            <div class="rounded-xl border border-red-200 bg-red-50 px-3 py-2.5">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-red-800">Delete room</p>
                <p class="mt-1 font-mono text-sm font-bold text-gray-900 select-all">{{ $moderationCodes['delete'] ?? '' }}</p>
                <p class="mt-0.5 text-[11px] text-red-800/80">Removes this room for everyone.</p>
            </div>
            <div class="rounded-xl border border-fuchsia-200 bg-fuchsia-50 px-3 py-2.5">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-fuchsia-800">Gibberish (1 hour)</p>
                <p class="mt-1 font-mono text-sm font-bold text-gray-900 select-all">{{ $moderationCodes['gibberish'] ?? '' }}</p>
                <p class="mt-0.5 text-[11px] text-fuchsia-800/80">Scrambles all message text for 1 hour.</p>
            </div>
            @if(!empty($moderationCodes['password']))
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-700">Room password</p>
                    <p class="mt-1 font-mono text-sm font-bold text-gray-900 select-all">{{ $moderationCodes['password'] }}</p>
                    <p class="mt-0.5 text-[11px] text-slate-600">Only you can view this again as the creator. Share it to let others join.</p>
                </div>
            @endif
            <p class="text-[11px] text-gray-500">These codes are also recorded for admins. Do not share them unless you intend to use them.</p>
        </div>
        <div class="px-5 py-3 border-t border-gray-100 flex justify-end">
            <button type="button" id="say-it-codes-dismiss" class="inline-flex items-center justify-center rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-700 transition">
                I saved them
            </button>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
(function () {
    const messagesUrl = @json(route('say-it.chat.messages', $room));
    const sendUrl = @json(route('say-it.chat.send', $room));
    const deleteUrlTemplate = @json(url('/Say-it/chat/'.$room->slug.'/messages/__ID__'));
    const lobbyUrl = @json(route('say-it.chat.index'));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const initialMessages = @json($initialMessages);
    let roomStatus = @json($roomStatus ?? ['is_frozen' => false, 'gibberish_active' => false, 'gibberish_until' => null]);
    const avatarMap = @json(
        collect($initialMessages)->pluck('codename')->unique()->mapWithKeys(function ($name) {
            return [$name => [
                'icon' => \App\Helpers\SayItHelper::animalIconForCodename($name),
                'color' => \App\Helpers\SayItHelper::avatarColorClassesForCodename($name),
            ]];
        })
    );

    const animalIcons = ['fa-dog','fa-cat','fa-dove','fa-fish','fa-frog','fa-hippo','fa-horse','fa-otter','fa-paw','fa-spider','fa-kiwi-bird','fa-crow'];
    const colorPairs = [
        'bg-sky-100 text-sky-600','bg-violet-100 text-violet-600','bg-rose-100 text-rose-600',
        'bg-amber-100 text-amber-700','bg-emerald-100 text-emerald-600','bg-indigo-100 text-indigo-600',
        'bg-fuchsia-100 text-fuchsia-600','bg-teal-100 text-teal-600','bg-orange-100 text-orange-600',
        'bg-cyan-100 text-cyan-600','bg-lime-100 text-lime-700','bg-pink-100 text-pink-600'
    ];

    function hashSeed(str) {
        let h = 0;
        const s = String(str || '?').toLowerCase();
        for (let i = 0; i < s.length; i++) {
            h = ((h << 5) - h) + s.charCodeAt(i);
            h |= 0;
        }
        return Math.abs(h);
    }

    function avatarFor(codename) {
        if (avatarMap[codename]) return avatarMap[codename];
        const h = hashSeed(codename);
        const value = {
            icon: 'fas ' + animalIcons[h % animalIcons.length],
            color: colorPairs[h % colorPairs.length],
        };
        avatarMap[codename] = value;
        return value;
    }

    const listEl = document.getElementById('say-it-chat-messages');
    const emptyEl = document.getElementById('say-it-chat-empty');
    const form = document.getElementById('say-it-chat-form');
    const input = document.getElementById('say-it-chat-input');
    const sendBtn = document.getElementById('say-it-chat-send');
    const errorEl = document.getElementById('say-it-chat-error');
    const imageInput = document.getElementById('say-it-chat-image');
    const imagePreview = document.getElementById('say-it-image-preview');
    const imagePreviewImg = document.getElementById('say-it-image-preview-img');
    const imagePreviewName = document.getElementById('say-it-image-preview-name');
    const imagePreviewClear = document.getElementById('say-it-image-preview-clear');
    const statusFrozen = document.getElementById('say-it-status-frozen');
    const statusGibberish = document.getElementById('say-it-status-gibberish');
    const statusWrap = document.getElementById('say-it-room-status');
    const codesModal = document.getElementById('say-it-codes-modal');
    const codesDismiss = document.getElementById('say-it-codes-dismiss');

    let lastId = 0;
    let polling = false;
    let sending = false;
    let pendingImage = null;
    let pendingImageUrl = null;
    const rendered = new Set();
    const imageExpiryTimers = new Map();

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function applyRoomStatus(status) {
        if (!status) return;
        const wasGibberish = !!(roomStatus && roomStatus.gibberish_active);
        roomStatus = status;
        const frozen = !!status.is_frozen;
        const gibberish = !!status.gibberish_active;

        if (statusFrozen) statusFrozen.classList.toggle('hidden', !frozen);
        if (statusGibberish) statusGibberish.classList.toggle('hidden', !gibberish);
        if (statusWrap) statusWrap.classList.toggle('pt-3', frozen || gibberish);
        if (form) {
            form.classList.toggle('opacity-60', frozen);
            form.classList.toggle('pointer-events-none', frozen);
        }
        if (input) {
            input.disabled = frozen;
            input.placeholder = frozen ? 'Room is frozen…' : 'Write a message…';
        }
        if (sendBtn) sendBtn.disabled = frozen;
        if (imageInput) imageInput.disabled = frozen;

        if (gibberish && !wasGibberish) {
            window.location.reload();
        }
    }

    function scrollToBottom(force) {
        const nearBottom = listEl.scrollHeight - listEl.scrollTop - listEl.clientHeight < 140;
        if (force || nearBottom) {
            listEl.scrollTop = listEl.scrollHeight;
        }
    }

    function clearPendingImage() {
        pendingImage = null;
        if (pendingImageUrl) {
            URL.revokeObjectURL(pendingImageUrl);
            pendingImageUrl = null;
        }
        if (imageInput) imageInput.value = '';
        if (imagePreview) imagePreview.classList.remove('is-visible');
        if (imagePreviewImg) imagePreviewImg.removeAttribute('src');
    }

    function scheduleImageExpiry(messageId, expiresAt) {
        if (!expiresAt || imageExpiryTimers.has(messageId)) return;
        const ms = new Date(expiresAt).getTime() - Date.now();
        if (ms <= 0) {
            expireMessageImage(messageId);
            return;
        }
        const timer = setTimeout(function () {
            expireMessageImage(messageId);
        }, Math.min(ms + 250, 2147483647));
        imageExpiryTimers.set(messageId, timer);
    }

    function expireMessageImage(messageId) {
        imageExpiryTimers.delete(messageId);
        const row = listEl.querySelector('[data-message-id="' + messageId + '"]');
        if (!row) return;
        const imageBlock = row.querySelector('[data-image-block]');
        if (imageBlock) imageBlock.remove();
        const bodyEl = row.querySelector('[data-msg-body]');
        const hasBody = bodyEl && (bodyEl.textContent || '').trim() !== '';
        if (!hasBody) {
            row.remove();
            rendered.delete(Number(messageId));
            if (rendered.size === 0 && emptyEl) emptyEl.classList.remove('hidden');
        }
    }

    function renderMessage(msg) {
        if (!msg || !msg.id || rendered.has(msg.id)) return;
        rendered.add(msg.id);
        if (msg.id > lastId) lastId = msg.id;

        const av = avatarFor(msg.codename || 'Anonymous');
        const wrap = document.createElement('div');
        wrap.className = 'say-it-msg-row' + (msg.is_own ? ' is-own' : '');
        wrap.dataset.messageId = String(msg.id);

        const hasImage = !!(msg.image_url);
        const hasBody = !!(msg.body && String(msg.body).trim());
        const bubbleClass = 'say-it-msg-bubble ' + (msg.is_own ? 'is-own' : 'is-other') + (hasImage && !hasBody ? ' has-image-only' : '');

        let contentHtml = '';
        if (hasImage) {
            contentHtml += `
                <div data-image-block>
                    <div class="say-it-msg-image-wrap ${hasBody ? 'mb-2' : ''}">
                        <a href="${escapeHtml(msg.image_url)}" target="_blank" rel="noopener noreferrer">
                            <img src="${escapeHtml(msg.image_url)}" alt="Shared image" class="say-it-msg-image">
                        </a>
                    </div>
                    <p class="say-it-msg-image-meta ${msg.is_own ? 'text-violet-100' : 'text-gray-400'}">Expires in 1 hour</p>
                </div>
            `;
        }
        if (hasBody) {
            contentHtml += `<div data-msg-body>${escapeHtml(msg.body)}</div>`;
        }

        wrap.innerHTML = `
            <div class="say-it-msg-avatar ${escapeHtml(av.color)}" aria-hidden="true">
                <i class="${escapeHtml(av.icon)}"></i>
            </div>
            <div class="say-it-msg-stack">
                <div class="say-it-msg-meta">
                    <span class="text-[11px] font-semibold ${msg.is_own ? 'text-violet-700' : 'text-gray-700'}">${escapeHtml(msg.codename || 'Anonymous')}</span>
                    <span class="text-[10px] text-gray-400">${escapeHtml(msg.created_at_human || '')}</span>
                </div>
                <div class="${bubbleClass}">${contentHtml}</div>
                ${msg.is_own ? `<button type="button" data-delete-id="${msg.id}" class="mt-1 text-[10px] text-gray-400 hover:text-red-500 transition px-1">Undo send</button>` : ''}
            </div>
        `;

        if (emptyEl) emptyEl.classList.add('hidden');
        listEl.appendChild(wrap);
        if (hasImage && msg.image_expires_at) {
            scheduleImageExpiry(msg.id, msg.image_expires_at);
        }
    }

    function setError(message) {
        if (!errorEl) return;
        if (!message) {
            errorEl.classList.add('hidden');
            errorEl.textContent = '';
            return;
        }
        errorEl.textContent = message;
        errorEl.classList.remove('hidden');
    }

    function hydrate(messages, isInitial) {
        (messages || []).forEach(renderMessage);
        if (rendered.size === 0 && emptyEl) emptyEl.classList.remove('hidden');
        scrollToBottom(!!isInitial);
    }

    async function poll() {
        if (polling) return;
        polling = true;
        try {
            const url = messagesUrl + (lastId > 0 ? ('?after_id=' + lastId) : '');
            const res = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            if (res.status === 404) {
                window.location.href = lobbyUrl;
                return;
            }
            if (!res.ok) return;
            const data = await res.json();
            if (data.room) applyRoomStatus(data.room);
            if (Array.isArray(data.messages) && data.messages.length) {
                hydrate(data.messages, false);
            }
        } catch (e) {
        } finally {
            polling = false;
        }
    }

    if (codesDismiss && codesModal) {
        codesDismiss.addEventListener('click', function () {
            codesModal.remove();
        });
    }

    if (imageInput) {
        imageInput.addEventListener('change', function () {
            const file = imageInput.files && imageInput.files[0];
            if (!file) {
                clearPendingImage();
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                setError('Images may not be larger than 5 MB.');
                clearPendingImage();
                return;
            }
            pendingImage = file;
            if (pendingImageUrl) URL.revokeObjectURL(pendingImageUrl);
            pendingImageUrl = URL.createObjectURL(file);
            if (imagePreviewImg) imagePreviewImg.src = pendingImageUrl;
            if (imagePreviewName) imagePreviewName.textContent = file.name || 'Image';
            if (imagePreview) imagePreview.classList.add('is-visible');
            setError('');
        });
    }

    if (imagePreviewClear) {
        imagePreviewClear.addEventListener('click', function () {
            clearPendingImage();
        });
    }

    listEl.addEventListener('click', async function (e) {
        const btn = e.target.closest('[data-delete-id]');
        if (!btn) return;
        const id = btn.getAttribute('data-delete-id');
        if (!id || !confirm('Remove this message?')) return;
        try {
            const res = await fetch(deleteUrlTemplate.replace('__ID__', id), {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });
            const data = await res.json().catch(function () { return {}; });
            if (!res.ok) {
                setError(data.message || 'Could not delete message.');
                return;
            }
            const row = listEl.querySelector('[data-message-id="' + id + '"]');
            if (row) row.remove();
            rendered.delete(Number(id));
            if (imageExpiryTimers.has(Number(id))) {
                clearTimeout(imageExpiryTimers.get(Number(id)));
                imageExpiryTimers.delete(Number(id));
            }
            if (rendered.size === 0 && emptyEl) emptyEl.classList.remove('hidden');
            setError('');
        } catch (err) {
            setError('Could not delete message.');
        }
    });

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (sending || (roomStatus && roomStatus.is_frozen)) return;
        const body = (input.value || '').trim();
        if (!body && !pendingImage) return;

        sending = true;
        sendBtn.disabled = true;
        setError('');

        try {
            const fd = new FormData();
            fd.append('body', body);
            if (pendingImage) fd.append('image', pendingImage);

            const res = await fetch(sendUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: fd,
            });
            const data = await res.json().catch(function () { return {}; });

            if (data.effect === 'delete') {
                window.location.href = data.redirect || lobbyUrl;
                return;
            }

            if (data.effect === 'freeze' || data.effect === 'gibberish') {
                input.value = '';
                input.style.height = 'auto';
                clearPendingImage();
                if (data.room) applyRoomStatus(data.room);
                setError(data.message_text || 'Moderation code applied.');
                return;
            }

            if (!res.ok) {
                if (data.room) applyRoomStatus(data.room);
                setError(
                    data.message
                    || (data.errors && data.errors.image && data.errors.image[0])
                    || (data.errors && data.errors.body && data.errors.body[0])
                    || 'Failed to send.'
                );
                return;
            }
            input.value = '';
            input.style.height = 'auto';
            clearPendingImage();
            if (data.room) applyRoomStatus(data.room);
            if (data.message && data.message.id) {
                renderMessage(data.message);
                scrollToBottom(true);
            }
        } catch (err) {
            setError('Failed to send. Check your connection.');
        } finally {
            sending = false;
            if (!(roomStatus && roomStatus.is_frozen)) {
                sendBtn.disabled = false;
                input.focus();
            }
        }
    });

    input.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 112) + 'px';
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.requestSubmit();
        }
    });

    applyRoomStatus(roomStatus);
    hydrate(initialMessages || [], true);
    setInterval(poll, 2500);
    if (!(roomStatus && roomStatus.is_frozen)) input.focus();
})();
</script>
@endpush
