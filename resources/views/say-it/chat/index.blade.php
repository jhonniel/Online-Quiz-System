@extends('layouts.say-it')

@section('title', 'Anonymous Chat – Say it')
@section('main_class', 'p-0 overflow-hidden flex flex-col')
@section('hide_flash')
@endsection

@section('content')
<div class="flex flex-col flex-1 min-h-0 h-full w-full bg-[#f3f4f6]">
    <div class="flex-1 min-h-0 overflow-y-auto scrollbar-hide">
        <div class="w-full h-full min-h-full px-4 sm:px-6 lg:px-8 py-4 sm:py-6 flex flex-col gap-5">
            @if(session('success'))
                <div class="flex-shrink-0 py-3 px-4 rounded-xl bg-emerald-50 text-emerald-800 text-sm font-medium border border-emerald-200">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="flex-shrink-0 py-3 px-4 rounded-xl bg-red-50 text-red-800 text-sm font-medium border border-red-200">{{ session('error') }}</div>
            @endif

            {{-- Hero / identity --}}
            <section class="relative overflow-hidden rounded-2xl border border-violet-100 bg-gradient-to-br from-violet-600 via-violet-600 to-indigo-700 text-white shadow-sm flex-shrink-0">
                <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, #fff 0, transparent 40%), radial-gradient(circle at 80% 0%, #c4b5fd 0, transparent 35%);"></div>
                <div class="relative px-5 sm:px-8 py-5 sm:py-7">
                    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-5">
                        <div class="min-w-0 max-w-2xl">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-violet-200">Say it · Live rooms</p>
                            <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight">Anonymous group chat</h1>
                            <p class="mt-2 text-sm sm:text-base text-violet-100/90 leading-relaxed">
                                Create a room or join an open conversation. Everyone appears under a temporary codename — no account required.
                            </p>
                        </div>
                        <div class="shrink-0 flex items-center gap-3 lg:pb-1">
                            <span class="text-[10px] uppercase tracking-wide text-violet-200 font-semibold">You are</span>
                            <span class="inline-flex items-center gap-2 rounded-full bg-white/15 backdrop-blur px-3 py-1.5 text-xs sm:text-sm font-semibold ring-1 ring-white/25">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full {{ \App\Helpers\SayItHelper::avatarColorClassesForCodename($codename) }} text-[11px]">
                                    <i class="{{ \App\Helpers\SayItHelper::animalIconForCodename($codename) }}"></i>
                                </span>
                                <span class="max-w-[12rem] truncate">{{ $codename }}</span>
                            </span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('say-it.chat.store') }}" class="mt-5 flex flex-col sm:flex-row gap-2 max-w-3xl">
                        @csrf
                        <div class="flex-1 min-w-0">
                            <label for="chat-room-name" class="sr-only">Room name</label>
                            <input id="chat-room-name"
                                   type="text"
                                   name="name"
                                   value="{{ old('name') }}"
                                   maxlength="80"
                                   required
                                   placeholder="Name your room…"
                                   class="w-full rounded-xl border-0 bg-white/95 px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm focus:ring-2 focus:ring-white/80">
                        </div>
                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-semibold text-violet-700 hover:bg-violet-50 active:scale-[0.98] transition shadow-sm">
                            <i class="fas fa-plus text-xs"></i>
                            Create room
                        </button>
                    </form>
                    @error('name')
                        <p class="mt-2 text-sm text-amber-100">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            {{-- Room tiles --}}
            <section class="flex-1 flex flex-col min-h-[18rem]">
                <div class="px-0.5 pb-3 flex items-center justify-between flex-shrink-0">
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
                               class="group flex flex-col rounded-2xl border border-gray-200/80 bg-white p-4 shadow-sm hover:border-violet-200 hover:shadow-md hover:bg-violet-50/30 transition">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-100 to-indigo-100 text-violet-700 ring-1 ring-violet-100 group-hover:from-violet-200 group-hover:to-indigo-200 transition">
                                        <i class="fas fa-hashtag text-sm"></i>
                                    </div>
                                    @if($room->last_message_at && $room->last_message_at->gt(now()->subMinutes(15)))
                                        <span class="shrink-0 inline-flex items-center gap-1 rounded-full bg-emerald-50 text-emerald-700 px-1.5 py-0.5 text-[10px] font-semibold">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Live
                                        </span>
                                    @endif
                                </div>
                                <p class="mt-3 text-sm font-semibold text-gray-900 line-clamp-2 group-hover:text-violet-700 transition leading-snug">{{ $room->name }}</p>
                                <p class="mt-1.5 text-xs text-gray-500 line-clamp-1">
                                    <span class="font-medium text-gray-600">{{ $room->creator_codename }}</span>
                                </p>
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
                                    <span class="text-[11px] font-semibold text-violet-600 opacity-70 group-hover:opacity-100 inline-flex items-center gap-1">
                                        Enter
                                        <i class="fas fa-arrow-right text-[9px] transition group-hover:translate-x-0.5"></i>
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="flex-1 flex flex-col items-center justify-center rounded-2xl border border-gray-200/80 bg-white px-5 py-20 text-center shadow-sm">
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
