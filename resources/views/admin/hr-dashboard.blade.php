@extends('layouts.admin')

@section('page-title', 'HR Dashboard')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">Dashboard</span>
        </div>
    </li>
@endsection

@section('content')
<div class="mx-2 sm:mx-3 lg:mx-4 xl:mx-6 space-y-6">
    <div class="bg-gradient-to-r from-teal-600 to-indigo-600 rounded-xl shadow-sm p-5 sm:p-6 text-white">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-teal-100">HR Dashboard</p>
                <h1 class="mt-1 text-2xl font-bold">Welcome, {{ $user->name }}</h1>
                <p class="mt-2 text-sm text-teal-50 max-w-2xl">
                    Quick access to the admin areas assigned to your HR account.
                    You have {{ $totalLinks }} feature{{ $totalLinks === 1 ? '' : 's' }} available.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.my-permissions') }}"
                   class="inline-flex items-center px-4 py-2 rounded-lg border border-white/25 bg-white/10 text-sm font-medium hover:bg-white/20">
                    My Permissions
                </a>
                <a href="{{ route('profile.edit') }}"
                   class="inline-flex items-center px-4 py-2 rounded-lg bg-white text-teal-700 text-sm font-semibold hover:bg-teal-50">
                    Edit Profile
                </a>
            </div>
        </div>
    </div>

    @if(count($stats) > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach($stats as $stat)
                @php
                    $toneClasses = match ($stat['tone']) {
                        'amber' => 'border-amber-200 bg-amber-50 text-amber-900',
                        'blue' => 'border-blue-200 bg-blue-50 text-blue-900',
                        'indigo' => 'border-indigo-200 bg-indigo-50 text-indigo-900',
                        'red' => 'border-red-200 bg-red-50 text-red-900',
                        default => 'border-gray-200 bg-white text-gray-900',
                    };
                @endphp
                <div class="rounded-xl border p-4 {{ $toneClasses }}">
                    <p class="text-xs font-medium uppercase tracking-wide opacity-80">{{ $stat['label'] }}</p>
                    <p class="mt-2 text-2xl font-bold">{{ $stat['value'] }}</p>
                    @if(!empty($stat['url']))
                        <a href="{{ $stat['url'] }}" class="mt-3 inline-flex text-xs font-semibold underline hover:no-underline">
                            Open
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if(count($sections) === 0)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-6 text-center">
            <p class="text-sm font-medium text-amber-900">No admin features are assigned to your HR account yet.</p>
            <p class="mt-2 text-sm text-amber-800">Contact an administrator to grant the permissions you need.</p>
        </div>
    @else
        <div class="space-y-6">
            @foreach($sections as $section)
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <div class="px-4 sm:px-5 py-3 border-b border-gray-100 bg-gray-50">
                        <h2 class="text-sm font-semibold text-gray-900">{{ $section['area'] }}</h2>
                    </div>
                    <div class="p-4 sm:p-5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                        @foreach($section['links'] as $link)
                            <a href="{{ $link['url'] }}"
                               class="group rounded-lg border border-gray-200 p-4 hover:border-indigo-300 hover:bg-indigo-50/40 transition-colors">
                                <p class="text-sm font-semibold text-gray-900 group-hover:text-indigo-700">{{ $link['label'] }}</p>
                                <p class="mt-1 text-xs text-gray-500 leading-relaxed">{{ $link['description'] }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
