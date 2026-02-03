@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h1 class="text-2xl font-bold text-white">Applicants</h1>
                    <p class="text-indigo-100">Review all applicants and their quiz performance</p>
                </div>
            </div>
            <a href="{{ route('admin.hiring-process.index') }}" class="inline-flex items-center px-4 py-2 border border-white border-opacity-20 rounded-md text-sm font-medium text-white hover:bg-white hover:bg-opacity-10">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Hiring Process
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div id="filters-section" class="bg-white shadow-sm rounded-lg border border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <span class="text-sm font-medium text-gray-700">Minimum Score: <span class="text-indigo-600">{{ $minimumScore }}%</span></span>
                @if($autoApproveScore)
                    <span class="text-sm font-medium text-gray-700">Auto-Approve: <span class="text-green-600">{{ $autoApproveScore }}%</span></span>
                @endif
            </div>
            <div class="text-sm text-gray-500">
                Total: {{ count($applicants) }} applicants
            </div>
        </div>
    </div>

    <!-- Applicants Table -->
    <div id="applications-section" class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Applications</h2>
            <p class="text-sm text-gray-500 mt-1">Applicants pending review or awaiting action</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Applicant
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Best Score
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Quiz
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Attempts
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Last Attempt
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($applicants as $applicant)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <span class="text-indigo-600 font-medium text-sm">{{ substr($applicant['name'], 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $applicant['name'] }}</div>
                                        <div class="text-sm text-gray-500">{{ $applicant['email'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold {{ $applicant['best_score'] >= $minimumScore ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($applicant['best_score'], 1) }}%
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $applicant['best_quiz'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ $applicant['attempts_count'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($applicant['status'] == 'auto_approved')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Auto Approved
                                    </span>
                                @elseif($applicant['status'] == 'pending_review')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Pending Review
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Failed
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($applicant['last_attempt'])
                                    {{ $applicant['last_attempt']->format('M j, Y') }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.users.show', $applicant['id']) }}" class="text-indigo-600 hover:text-indigo-900">
                                    View Profile
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No applicants found</h3>
                                <p class="mt-1 text-sm text-gray-500">No pending applicants at this time.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Hired Applicants Table -->
    <div id="hired-applicants" class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden mt-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Hired Applicants</h2>
            <p class="text-sm text-gray-500 mt-1">Applicants who have been hired or accepted</p>
        </div>

        <!-- Filters for Hired Applicants -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <form method="GET" action="{{ route('admin.hiring-process.applicants') }}" id="hired-applicants-form" class="flex items-center justify-between flex-wrap gap-4">
                <!-- Preserve other query parameters -->
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                @if(request('position'))
                    <input type="hidden" name="position" value="{{ request('position') }}">
                @endif
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif

                <div class="flex items-center space-x-4 flex-wrap flex-1">
                    <!-- Search -->
                    <div class="flex-1 min-w-[240px] max-w-md">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text"
                                   id="hired-search-input"
                                   name="hired_search"
                                   value="{{ request('hired_search', $hiredSearch ?? '') }}"
                                   placeholder="Search hired applicants (name, email, position, status, ID)..."
                                   autocomplete="off"
                                   class="block w-full pl-9 pr-10 py-2 border border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @if(request('hired_search'))
                                <button type="button"
                                        id="hired-clear-search-btn"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                                        title="Clear search">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Position Filter -->
                    @if(isset($positions) && $positions->count() > 0)
                        <div class="flex items-center space-x-2">
                            <label for="hired_position" class="text-sm font-medium text-gray-700">Position:</label>
                            <select name="hired_position" id="hired_position" onchange="submitHiredForm()"
                                    class="px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All Positions</option>
                                @foreach($positions as $pos)
                                    <option value="{{ $pos->id }}" {{ ($hiredPositionFilter ?? '') == $pos->id ? 'selected' : '' }}>
                                        {{ $pos->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
                <div class="text-sm text-gray-500 whitespace-nowrap">
                    Total: {{ count($hiredApplicants) }} hired applicants
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Applicant
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Position
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Best Score
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Quiz
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Attempts
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Reviewed At
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($hiredApplicants as $applicant)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full {{ $applicant['status'] == 'hired' ? 'bg-green-100' : 'bg-blue-100' }} flex items-center justify-center">
                                            <span class="{{ $applicant['status'] == 'hired' ? 'text-green-600' : 'text-blue-600' }} font-medium text-sm">{{ substr($applicant['name'], 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $applicant['name'] }}</div>
                                        <div class="text-sm text-gray-500">{{ $applicant['email'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $applicant['position'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-green-600">
                                    {{ number_format($applicant['best_score'], 1) }}%
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $applicant['best_quiz'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ $applicant['attempts_count'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($applicant['status'] == 'hired')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Hired
                                    </span>
                                @elseif($applicant['status'] == 'accepted')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Accepted
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($applicant['reviewed_at'])
                                    {{ $applicant['reviewed_at']->format('M j, Y g:i A') }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if($applicant['application_id'])
                                    <a href="{{ route('admin.hiring-applications.show', $applicant['application_id']) }}" class="text-indigo-600 hover:text-indigo-900">
                                        View Application
                                    </a>
                                @elseif($applicant['id'])
                                    <a href="{{ route('admin.users.show', $applicant['id']) }}" class="text-indigo-600 hover:text-indigo-900">
                                        View Profile
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No hired applicants</h3>
                                <p class="mt-1 text-sm text-gray-500">No applicants have been hired or accepted yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if URL hash is #hired-applicants
    if (window.location.hash === '#hired-applicants') {
        // Hide the Applications section
        const applicationsSection = document.getElementById('applications-section');
        if (applicationsSection) {
            applicationsSection.style.display = 'none';
        }
        
        // Hide the filters section
        const filtersSection = document.getElementById('filters-section');
        if (filtersSection) {
            filtersSection.style.display = 'none';
        }
        
        // Scroll to hired applicants section
        setTimeout(function() {
            const hiredSection = document.getElementById('hired-applicants');
            if (hiredSection) {
                hiredSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 100);
    }
    
    // Handle hash changes (if user clicks anchor link while on page)
    window.addEventListener('hashchange', function() {
        const applicationsSection = document.getElementById('applications-section');
        const filtersSection = document.getElementById('filters-section');
        
        if (window.location.hash === '#hired-applicants') {
            if (applicationsSection) {
                applicationsSection.style.display = 'none';
            }
            if (filtersSection) {
                filtersSection.style.display = 'none';
            }
        } else {
            if (applicationsSection) {
                applicationsSection.style.display = '';
            }
            if (filtersSection) {
                filtersSection.style.display = '';
            }
        }
    });

    // Hired Applicants Search Functionality
    const hiredForm = document.getElementById('hired-applicants-form');
    const hiredSearchInput = document.getElementById('hired-search-input');
    const hiredClearBtn = document.getElementById('hired-clear-search-btn');

    // Function to submit form with hash
    window.submitHiredForm = function() {
        const form = document.getElementById('hired-applicants-form');
        if (form) {
            // Build URL with query string and hash
            const formData = new FormData(form);
            const params = new URLSearchParams();
            
            // Add all form data to params
            for (const [key, value] of formData.entries()) {
                if (value) {
                    params.append(key, value);
                }
            }
            
            // Build URL with hash
            const url = form.action + '?' + params.toString() + '#hired-applicants';
            window.location.href = url;
        }
    };

    let hiredSearchTimeout = null;
    if (hiredForm && hiredSearchInput) {
        hiredSearchInput.addEventListener('input', function () {
            if (hiredSearchTimeout) clearTimeout(hiredSearchTimeout);
            hiredSearchTimeout = setTimeout(() => {
                submitHiredForm();
            }, 350);
        });
    }

    if (hiredClearBtn && hiredSearchInput && hiredForm) {
        hiredClearBtn.addEventListener('click', function () {
            hiredSearchInput.value = '';
            submitHiredForm();
        });
    }

    // Update form action to include hash on page load if we're on hired applicants section
    if (hiredForm && window.location.hash === '#hired-applicants') {
        // Form will submit with hash preserved via JavaScript
    }
});
</script>
@endsection

