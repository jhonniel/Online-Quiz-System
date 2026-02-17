@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('admin.hiring-positions.index') }}" class="mr-4 text-white hover:text-indigo-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-white">{{ $hiringPosition->title }}</h1>
                    <p class="text-indigo-100">Position Details</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.hiring-positions.edit', $hiringPosition) }}" class="inline-flex items-center px-4 py-2 border border-white border-opacity-20 rounded-md text-sm font-medium text-white hover:bg-white hover:bg-opacity-10">
                    Edit
                </a>
                <a href="{{ $hiringPosition->url }}" target="_blank" class="inline-flex items-center px-4 py-2 border border-white border-opacity-20 rounded-md text-sm font-medium text-white hover:bg-white hover:bg-opacity-10">
                    View Public Page
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Position Details -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Position Information</h2>
                </div>
                <div class="px-6 py-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Department</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $hiringPosition->department ?: 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Location</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $hiringPosition->location ?: 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Employment Type</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $hiringPosition->employment_type ?: 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Status</label>
                            <p class="mt-1">
                                @if($hiringPosition->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Inactive
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($hiringPosition->salary_min || $hiringPosition->salary_max)
                        <div>
                            <label class="text-sm font-medium text-gray-500">Salary Range</label>
                            <p class="mt-1 text-sm text-gray-900">
                                @if($hiringPosition->salary_min && $hiringPosition->salary_max)
                                    ${{ number_format($hiringPosition->salary_min) }} - ${{ number_format($hiringPosition->salary_max) }}
                                @elseif($hiringPosition->salary_min)
                                    From ${{ number_format($hiringPosition->salary_min) }}
                                @elseif($hiringPosition->salary_max)
                                    Up to ${{ number_format($hiringPosition->salary_max) }}
                                @endif
                            </p>
                        </div>
                    @endif

                    @if($hiringPosition->application_deadline)
                        <div>
                            <label class="text-sm font-medium text-gray-500">Application Deadline</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $hiringPosition->application_deadline->format('M j, Y') }}</p>
                        </div>
                    @endif

                    @if($hiringPosition->description)
                        <div>
                            <label class="text-sm font-medium text-gray-500">Description</label>
                            <p class="mt-1 text-sm text-gray-700 whitespace-pre-wrap">{{ $hiringPosition->description }}</p>
                        </div>
                    @endif

                    @if($hiringPosition->requirements)
                        <div>
                            <label class="text-sm font-medium text-gray-500">Requirements</label>
                            <p class="mt-1 text-sm text-gray-700 whitespace-pre-wrap">{{ $hiringPosition->requirements }}</p>
                        </div>
                    @endif

                    @if($hiringPosition->responsibilities)
                        <div>
                            <label class="text-sm font-medium text-gray-500">Responsibilities</label>
                            <p class="mt-1 text-sm text-gray-700 whitespace-pre-wrap">{{ $hiringPosition->responsibilities }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Applications -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Applications ({{ $hiringPosition->applications->count() }})</h2>
                </div>
                <div class="px-6 py-6">
                    @if($hiringPosition->applications->count() > 0)
                        <div class="space-y-3">
                            @foreach($hiringPosition->applications->take(5) as $application)
                                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $application->full_name }}</p>
                                        <p class="text-sm text-gray-500">{{ $application->email }}</p>
                                    </div>
                                    <a href="{{ route('admin.hiring-applications.show', $application) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">
                                        View
                                    </a>
                                </div>
                            @endforeach
                            @if($hiringPosition->applications->count() > 5)
                                <a href="{{ route('admin.hiring-applications.index', ['position' => $hiringPosition->id]) }}" class="block text-center text-sm text-indigo-600 hover:text-indigo-900">
                                    View all {{ $hiringPosition->applications->count() }} applications
                                </a>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No applications yet.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Info -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Quick Info</h2>
                </div>
                <div class="px-6 py-6 space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">URL Slug</label>
                        <p class="mt-1 text-sm text-gray-900 font-mono">{{ $hiringPosition->slug }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Public URL</label>
                        <a href="{{ $hiringPosition->url }}" target="_blank" class="mt-1 text-sm text-indigo-600 hover:text-indigo-900 block break-all">
                            {{ $hiringPosition->url }}
                        </a>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Created</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $hiringPosition->created_at->format('M j, Y') }}</p>
                    </div>
                    @if($hiringPosition->creator)
                        <div>
                            <label class="text-sm font-medium text-gray-500">Created By</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $hiringPosition->creator->name }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

