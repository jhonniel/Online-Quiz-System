@extends('layouts.say-it')

@section('title', 'Unlock – '.$room->name)
@section('main_class', 'p-0 overflow-hidden flex flex-col')

@section('content')
<div class="flex flex-1 min-h-0 h-full w-full items-center justify-center bg-[#f3f4f6] px-4 py-8">
    <div class="w-full max-w-md rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-violet-50">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-violet-600">Password protected</p>
            <h1 class="mt-1 text-lg font-bold text-gray-900 truncate">{{ $room->name }}</h1>
            <p class="mt-1 text-xs text-gray-500">Enter the room password each time you open this chat.</p>
        </div>
        <form method="POST" action="{{ route('say-it.chat.unlock', $room) }}" class="px-5 py-5 space-y-4">
            @csrf
            @if(!empty($isRoomOwner) && !empty($ownerPassword))
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-600">Your room password</p>
                    <p class="mt-1 font-mono text-sm font-bold text-gray-900 select-all">{{ $ownerPassword }}</p>
                    <p class="mt-0.5 text-[11px] text-slate-500">Only visible to you as the creator.</p>
                </div>
            @endif
            <div>
                <label for="room-password" class="block text-xs font-semibold text-gray-700 mb-1.5">Room password</label>
                <input id="room-password"
                       type="password"
                       name="password"
                       required
                       maxlength="64"
                       autofocus
                       autocomplete="current-password"
                       class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 focus:border-violet-400 focus:ring-violet-500/30"
                       placeholder="Enter password">
                @error('password')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-violet-600 px-4 py-3 text-sm font-semibold text-white hover:bg-violet-700 transition">
                <i class="fas fa-unlock-alt text-xs"></i>
                Unlock room
            </button>
            <a href="{{ route('say-it.chat.index') }}" class="block text-center text-xs font-medium text-gray-500 hover:text-violet-700">
                ← Back to rooms
            </a>
        </form>
    </div>
</div>
@endsection
