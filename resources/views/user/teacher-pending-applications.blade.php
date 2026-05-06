@extends('layouts.user')

@section('page-title', 'Pending Applications')

@section('content')
<div class="h-full flex flex-col min-h-0 min-w-0">
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 shadow-sm p-3 sm:p-4 flex-shrink-0">
        <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white break-words">Pending applications</h1>
        <p class="text-indigo-100 text-sm mt-0.5 break-words leading-snug">
            Read-only list of <span class="font-semibold text-white">pending</span> hiring applications from your school. Admins handle review.
            @if($schoolName)
                <span class="block mt-1 opacity-95">{{ $schoolName }}</span>
            @else
                <span class="block mt-1 text-amber-100">Assign a school on your teacher profile to see applicants who chose that school.</span>
            @endif
        </p>
    </div>

    <div class="bg-white border-t border-gray-200 flex-1 min-h-0 overflow-auto flex flex-col">
        @if($applications->total() === 0)
            <div class="p-8 text-center text-gray-500">
                @if(! $schoolName)
                    Assign a school on your profile to match applicants&apos; school selection on the hiring form.
                @else
                    No pending applications for your school right now.
                @endif
            </div>
        @else
            <div class="flex-1 min-h-0 overflow-x-auto overflow-y-auto" style="-webkit-overflow-scrolling: touch;">
                <table class="min-w-[36rem] w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th scope="col" class="px-3 sm:px-4 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applicant</th>
                            <th scope="col" class="px-3 sm:px-4 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                            <th scope="col" class="px-3 sm:px-4 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">School</th>
                            <th scope="col" class="px-3 sm:px-4 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Applied</th>
                            <th scope="col" class="px-3 sm:px-4 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach($applications as $app)
                            <tr class="hover:bg-gray-50/80">
                                <td class="px-3 sm:px-4 py-2.5 align-top">
                                    <p class="text-sm font-medium text-gray-900">{{ $app->full_name }}</p>
                                    <p class="text-xs text-gray-500 break-all">{{ $app->email }}</p>
                                    @if($app->phone)
                                        <p class="text-xs text-gray-500">{{ $app->phone }}</p>
                                    @endif
                                    <p class="sm:hidden text-[11px] text-gray-600 mt-1 break-words">{{ $app->school ?? '—' }}</p>
                                </td>
                                <td class="px-3 sm:px-4 py-2.5 align-top text-sm text-gray-900">
                                    @if($app->hiringPosition)
                                        <span class="font-medium">{{ $app->hiringPosition->title }}</span>
                                        @if($app->hiringPosition->employment_type ?? null)
                                            <span class="block text-xs text-gray-500">{{ $app->hiringPosition->employment_type }}</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 sm:px-4 py-2.5 align-top text-sm text-gray-700 hidden sm:table-cell break-words max-w-xs">
                                    {{ $app->school ?? '—' }}
                                </td>
                                <td class="px-3 sm:px-4 py-2.5 align-top text-sm text-gray-700 whitespace-nowrap">
                                    {{ optional($app->created_at)->format('M j, Y g:i A') }}
                                </td>
                                <td class="px-3 sm:px-4 py-2.5 align-top text-sm whitespace-nowrap">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-800">
                                        Pending
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($applications->hasPages())
                <div class="flex-shrink-0 px-3 sm:px-4 py-3 border-t border-gray-100 bg-gray-50 overflow-x-auto">
                    {{ $applications->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
