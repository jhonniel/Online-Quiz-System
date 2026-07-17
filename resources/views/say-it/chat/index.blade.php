@extends('layouts.say-it')

@section('title', 'Anonymous Chat – Say it')
@section('main_class', 'p-0 overflow-hidden flex flex-col')
@section('hide_flash')
@endsection

@push('styles')
<style>
    .say-it-lobby-hero {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 48%, #4338ca 100%);
    }
    .say-it-lobby-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        opacity: 0.22;
        background-image:
            radial-gradient(circle at 18% 20%, #fff 0, transparent 38%),
            radial-gradient(circle at 88% 8%, #c4b5fd 0, transparent 34%);
        pointer-events: none;
    }
    .say-it-avatar-pick:hover #chat-room-avatar-preview {
        background: rgba(255, 255, 255, 0.28);
        border-color: rgba(255, 255, 255, 0.55);
    }
</style>
@endpush

@section('content')
<div class="flex flex-col flex-1 min-h-0 h-full w-full bg-[#f3f4f6]">
    <div class="flex-1 min-h-0 overflow-y-auto scrollbar-hide">
        <div class="w-full max-w-6xl mx-auto h-full min-h-full px-4 sm:px-6 lg:px-8 py-5 sm:py-7 flex flex-col gap-6">
            @if(session('success'))
                <div class="flex-shrink-0 py-3 px-4 rounded-xl bg-emerald-50 text-emerald-800 text-sm font-medium border border-emerald-200">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="flex-shrink-0 py-3 px-4 rounded-xl bg-red-50 text-red-800 text-sm font-medium border border-red-200">{{ session('error') }}</div>
            @endif

            {{-- Purple hero + create --}}
            <section class="say-it-lobby-hero relative overflow-hidden rounded-2xl border border-violet-500/20 text-white shadow-sm flex-shrink-0">
                <div class="relative px-5 sm:px-7 py-5 sm:py-6">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div class="min-w-0 max-w-2xl">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-violet-200">Say it · Live rooms</p>
                            <h1 class="mt-1 text-2xl sm:text-[1.75rem] font-bold tracking-tight">Anonymous group chat</h1>
                            <p class="mt-1.5 text-sm text-violet-100/90 max-w-xl leading-relaxed">
                                Create a private or public room. Everyone joins under a temporary codename.
                            </p>
                        </div>
                        <div class="shrink-0 inline-flex items-center gap-3 rounded-2xl bg-white/12 ring-1 ring-white/25 backdrop-blur px-3.5 py-2.5">
                            <span class="text-[10px] uppercase tracking-wide text-violet-200 font-semibold">You are</span>
                            <span class="inline-flex items-center gap-2 min-w-0">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full text-xs {{ \App\Helpers\SayItHelper::avatarColorClassesForCodename($codename) }}">
                                    <i class="{{ \App\Helpers\SayItHelper::animalIconForCodename($codename) }}"></i>
                                </span>
                                <span class="text-sm font-semibold text-white max-w-[11rem] truncate">{{ $codename }}</span>
                            </span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('say-it.chat.store') }}" enctype="multipart/form-data" class="mt-5 rounded-2xl bg-white/10 ring-1 ring-white/20 backdrop-blur-sm px-4 sm:px-5 py-4">
                        @csrf
                        <div class="grid grid-cols-1 lg:grid-cols-[5.5rem_minmax(0,1fr)_minmax(0,14rem)_auto] gap-4 lg:gap-5 items-end">
                            <div>
                                <p class="text-[11px] font-semibold text-violet-100 mb-1.5">Photo</p>
                                <label for="chat-room-avatar" class="say-it-avatar-pick relative inline-flex cursor-pointer">
                                    <span id="chat-room-avatar-preview" class="flex h-[4.25rem] w-[4.25rem] items-center justify-center rounded-2xl border border-dashed border-white/35 bg-white/15 text-white overflow-hidden transition">
                                        <i class="fas fa-camera text-sm"></i>
                                    </span>
                                    <input id="chat-room-avatar"
                                           type="file"
                                           name="avatar"
                                           accept="image/jpeg,image/png,image/gif,image/webp"
                                           class="sr-only">
                                    <span class="absolute -bottom-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full bg-white text-violet-700 text-[10px] shadow-sm">
                                        <i class="fas fa-plus"></i>
                                    </span>
                                </label>
                            </div>

                            <div class="min-w-0">
                                <label for="chat-room-name" class="block text-[11px] font-semibold text-violet-100 mb-1.5">Room name</label>
                                <input id="chat-room-name"
                                       type="text"
                                       name="name"
                                       value="{{ old('name') }}"
                                       maxlength="80"
                                       required
                                       placeholder="e.g. Weekend thoughts"
                                       class="w-full rounded-xl border-0 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm focus:ring-2 focus:ring-white/80">
                            </div>

                            <div class="min-w-0">
                                <label for="chat-room-password" class="block text-[11px] font-semibold text-violet-100 mb-1.5">
                                    Password <span class="font-normal text-violet-200/80">(optional)</span>
                                </label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                                        <i class="fas fa-lock text-[11px]"></i>
                                    </span>
                                    <input id="chat-room-password"
                                           type="password"
                                           name="password"
                                           value="{{ old('password') }}"
                                           maxlength="64"
                                           autocomplete="new-password"
                                           placeholder="Min. 4 characters"
                                           class="w-full rounded-xl border-0 bg-white pl-9 pr-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm focus:ring-2 focus:ring-white/80">
                                </div>
                            </div>

                            <button type="submit"
                                    class="inline-flex w-full lg:w-auto items-center justify-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-violet-700 hover:bg-violet-50 active:scale-[0.98] transition shadow-sm">
                                Create room
                            </button>
                        </div>

                        <p class="mt-3 text-[11px] text-violet-100/80 leading-relaxed">
                            Photo: JPEG, PNG, GIF, or WebP · max 5 MB. Password is visible only to you as the creator.
                        </p>

                        @error('name')
                            <p class="mt-2 text-sm text-amber-100">{{ $message }}</p>
                        @enderror
                        @error('password')
                            <p class="mt-2 text-sm text-amber-100">{{ $message }}</p>
                        @enderror
                        @error('avatar')
                            <p class="mt-2 text-sm text-amber-100">{{ $message }}</p>
                        @enderror
                    </form>
                </div>
            </section>

            {{-- Room tiles --}}
            <section class="flex-1 flex flex-col min-h-[16rem]">
                <div class="pb-3 flex items-end justify-between gap-3 flex-shrink-0">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900">Open rooms</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Sorted by recent activity</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-violet-50 text-violet-700 px-2.5 py-0.5 text-[11px] font-semibold tabular-nums">
                        {{ $rooms->total() }}
                    </span>
                </div>

                @if($rooms->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4 flex-1 content-start">
                        @foreach($rooms as $room)
                            <a href="{{ route('say-it.chat.show', $room) }}"
                               class="group flex flex-col rounded-2xl border border-gray-200/90 bg-white p-4 shadow-sm hover:border-violet-200 hover:shadow-md transition">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl overflow-hidden ring-1 ring-black/5 {{ $room->avatar_url ? 'bg-violet-50' : \App\Helpers\SayItHelper::roomPlaceholderColorClasses($room->slug) }}">
                                        @if($room->avatar_url)
                                            <img src="{{ $room->avatar_url }}" alt="" class="h-full w-full object-cover">
                                        @else
                                            <i class="{{ \App\Helpers\SayItHelper::roomPlaceholderIcon($room->slug) }} text-sm"></i>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        @if($room->hasPassword())
                                            <span class="shrink-0 inline-flex items-center gap-1 rounded-md bg-slate-100 text-slate-600 px-1.5 py-0.5 text-[10px] font-semibold" title="Password protected">
                                                <i class="fas fa-lock text-[9px]"></i>
                                            </span>
                                        @endif
                                        @if($room->last_message_at && $room->last_message_at->gt(now()->subMinutes(15)))
                                            <span class="shrink-0 inline-flex items-center gap-1 rounded-md bg-emerald-50 text-emerald-700 px-1.5 py-0.5 text-[10px] font-semibold">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                Live
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <p class="mt-3 text-sm font-semibold text-gray-900 line-clamp-2 group-hover:text-violet-700 transition leading-snug">{{ $room->name }}</p>
                                <p class="mt-1 text-xs text-gray-500 truncate">{{ $room->creator_codename }}</p>
                                <div class="mt-auto pt-3 flex items-center justify-between gap-2 border-t border-gray-100">
                                    <span class="text-[11px] text-gray-500 tabular-nums">
                                        {{ $room->messages_count }} {{ $room->messages_count === 1 ? 'msg' : 'msgs' }}
                                        ·
                                        @if($room->last_message_at)
                                            {{ $room->last_message_at->diffForHumans(null, true) }}
                                        @else
                                            {{ $room->created_at->diffForHumans(null, true) }}
                                        @endif
                                    </span>
                                    <span class="text-[11px] font-semibold text-violet-600 opacity-0 group-hover:opacity-100 transition inline-flex items-center gap-1">
                                        Enter
                                        <i class="fas fa-arrow-right text-[9px]"></i>
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="flex-1 flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-300 bg-white/70 px-5 py-16 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-50 text-violet-600 mb-3">
                            <i class="fas fa-comments"></i>
                        </div>
                        <p class="text-sm font-medium text-gray-900">No rooms yet</p>
                        <p class="mt-1 text-sm text-gray-500">Create the first room to start a conversation.</p>
                    </div>
                @endif

                @if($rooms->hasPages())
                    <div class="pt-4 flex-shrink-0">
                        {{ $rooms->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var input = document.getElementById('chat-room-avatar');
    var preview = document.getElementById('chat-room-avatar-preview');
    if (!input || !preview) return;
    input.addEventListener('change', function () {
        var file = input.files && input.files[0];
        if (!file) return;
        var url = URL.createObjectURL(file);
        preview.innerHTML = '<img src="' + url + '" alt="" class="h-full w-full object-cover">';
        preview.classList.remove('border-dashed', 'bg-white/15');
        preview.classList.add('border-solid', 'border-white/50');
    });
})();
</script>
@endpush
