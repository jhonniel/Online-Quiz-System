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
        background: #fff;
    }
    #say-it-chat-panel {
        display: flex;
        flex-direction: column;
        height: 100%;
        min-height: 0;
        width: 100%;
        flex: 1 1 auto;
        background: #fff;
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
    .say-it-msg-stack { max-width: min(72%, 40rem); min-width: 0; }
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
    .say-it-composer {
        border-top: 1px solid #e5e7eb;
        background: rgba(255,255,255,0.98);
        backdrop-filter: blur(8px);
    }
    .say-it-composer-inner {
        width: 100%;
        max-width: none;
    }
</style>
@endpush

@section('content')
<div id="say-it-chat-app">
    <div id="say-it-chat-panel">
        {{-- Header --}}
        <header class="flex-shrink-0 border-b border-gray-200 bg-white px-4 sm:px-6 lg:px-8 py-3.5 flex items-center gap-3">
            <a href="{{ route('say-it.chat.index') }}"
               class="flex h-9 w-9 items-center justify-center rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-800 transition"
               aria-label="Back to rooms">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-indigo-600 text-white shadow-sm">
                <i class="fas fa-comments text-sm"></i>
            </div>
            <div class="min-w-0 flex-1">
                <h1 class="text-sm sm:text-base font-bold text-gray-900 truncate leading-tight">{{ $room->name }}</h1>
                <p class="text-[11px] sm:text-xs text-gray-500 truncate mt-0.5">
                    Anonymous room · chatting as
                    <span class="font-semibold text-violet-700">{{ $codename }}</span>
                </p>
            </div>
            <div class="hidden sm:flex shrink-0 items-center gap-2 rounded-full bg-gray-50 ring-1 ring-gray-200 px-2.5 py-1.5">
                <span class="flex h-6 w-6 items-center justify-center rounded-full text-[10px] {{ \App\Helpers\SayItHelper::avatarColorClassesForCodename($codename) }}">
                    <i class="{{ \App\Helpers\SayItHelper::animalIconForCodename($codename) }}"></i>
                </span>
                <span class="text-[11px] font-medium text-gray-600 max-w-[7rem] truncate">{{ $codename }}</span>
            </div>
        </header>

        {{-- Messages --}}
        <div id="say-it-chat-messages" class="px-4 sm:px-6 lg:px-8 py-5 space-y-4">
            <div id="say-it-chat-empty" class="hidden py-10 px-6 text-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-50 text-violet-600 mb-4 ring-1 ring-violet-100">
                    <i class="fas fa-comment-dots text-xl"></i>
                </div>
                <p class="text-sm font-semibold text-gray-900">Start the conversation</p>
                <p class="mt-1 text-sm text-gray-500 max-w-sm">Messages are anonymous. Be respectful — this room is open to everyone.</p>
            </div>
        </div>

        {{-- Composer --}}
        <form id="say-it-chat-form" class="say-it-composer flex-shrink-0 px-4 sm:px-6 lg:px-8 py-3 sm:py-4 pb-[max(0.75rem,env(safe-area-inset-bottom))]">
            <div class="say-it-composer-inner flex items-end gap-2 rounded-2xl border border-gray-200 bg-gray-50 focus-within:bg-white focus-within:ring-2 focus-within:ring-violet-500/30 focus-within:border-violet-400 transition px-3 py-2.5">
                <textarea id="say-it-chat-input"
                          rows="1"
                          maxlength="2000"
                          placeholder="Write a message…"
                          class="flex-1 min-w-0 resize-none border-0 bg-transparent px-1.5 py-1.5 text-sm sm:text-[0.9375rem] text-gray-900 placeholder:text-gray-400 focus:ring-0 max-h-28 leading-5"></textarea>
                <button type="submit"
                        id="say-it-chat-send"
                        class="inline-flex items-center justify-center h-10 w-10 rounded-xl bg-violet-600 text-white hover:bg-violet-700 disabled:opacity-40 disabled:cursor-not-allowed transition shadow-sm shrink-0"
                        aria-label="Send message">
                    <i class="fas fa-paper-plane text-xs"></i>
                </button>
            </div>
            <div class="mt-1.5 flex items-center justify-between gap-2 px-1">
                <p class="text-[10px] text-gray-400">Enter to send · Shift+Enter for new line</p>
                <p id="say-it-chat-error" class="hidden text-[11px] text-red-600 font-medium"></p>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const messagesUrl = @json(route('say-it.chat.messages', $room));
    const sendUrl = @json(route('say-it.chat.send', $room));
    const deleteUrlTemplate = @json(url('/Say-it/chat/'.$room->slug.'/messages/__ID__'));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const initialMessages = @json($initialMessages);
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

    let lastId = 0;
    let polling = false;
    let sending = false;
    const rendered = new Set();

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function scrollToBottom(force) {
        const nearBottom = listEl.scrollHeight - listEl.scrollTop - listEl.clientHeight < 140;
        if (force || nearBottom) {
            listEl.scrollTop = listEl.scrollHeight;
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

        wrap.innerHTML = `
            <div class="say-it-msg-avatar ${escapeHtml(av.color)}" aria-hidden="true">
                <i class="${escapeHtml(av.icon)}"></i>
            </div>
            <div class="say-it-msg-stack">
                <div class="say-it-msg-meta">
                    <span class="text-[11px] font-semibold ${msg.is_own ? 'text-violet-700' : 'text-gray-700'}">${escapeHtml(msg.codename || 'Anonymous')}</span>
                    <span class="text-[10px] text-gray-400">${escapeHtml(msg.created_at_human || '')}</span>
                </div>
                <div class="say-it-msg-bubble ${msg.is_own ? 'is-own' : 'is-other'}">${escapeHtml(msg.body || '')}</div>
                ${msg.is_own ? `<button type="button" data-delete-id="${msg.id}" class="mt-1 text-[10px] text-gray-400 hover:text-red-500 transition px-1">Undo send</button>` : ''}
            </div>
        `;

        if (emptyEl) emptyEl.classList.add('hidden');
        listEl.appendChild(wrap);
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
            if (!res.ok) return;
            const data = await res.json();
            if (Array.isArray(data.messages) && data.messages.length) {
                hydrate(data.messages, false);
            }
        } catch (e) {
        } finally {
            polling = false;
        }
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
            if (rendered.size === 0 && emptyEl) emptyEl.classList.remove('hidden');
            setError('');
        } catch (err) {
            setError('Could not delete message.');
        }
    });

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (sending) return;
        const body = (input.value || '').trim();
        if (!body) return;

        sending = true;
        sendBtn.disabled = true;
        setError('');

        try {
            const res = await fetch(sendUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ body: body }),
            });
            const data = await res.json().catch(function () { return {}; });
            if (!res.ok) {
                setError(data.message || (data.errors && data.errors.body && data.errors.body[0]) || 'Failed to send.');
                return;
            }
            input.value = '';
            input.style.height = 'auto';
            if (data.message) {
                renderMessage(data.message);
                scrollToBottom(true);
            }
        } catch (err) {
            setError('Failed to send. Check your connection.');
        } finally {
            sending = false;
            sendBtn.disabled = false;
            input.focus();
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

    hydrate(initialMessages || [], true);
    setInterval(poll, 2500);
    input.focus();
})();
</script>
@endpush
