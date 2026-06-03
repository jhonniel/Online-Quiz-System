@extends('layouts.user')

@section('page-title', 'My Students')

@section('content')
@php
    $violationBreakdowns = $violationBreakdowns ?? [];
@endphp
<div class="h-full flex flex-col min-h-0 min-w-0">
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 shadow-sm p-3 sm:p-4 flex-shrink-0">
        <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white break-words">My Students</h1>
        <p class="text-indigo-100 text-sm mt-0.5 break-words leading-snug">
            @if($schoolName)
                Students from {{ $schoolName }}
            @else
                Assign a school to this teacher account to view students
            @endif
        </p>
    </div>

    <div class="bg-white border-t border-gray-200 flex-1 min-h-0 overflow-auto">
        <div class="p-3 sm:p-4 border-b border-gray-100 bg-gray-50">
            <form method="GET" action="{{ url('/teacher/students') }}" class="flex flex-col sm:flex-row gap-2">
                <input
                    type="text"
                    name="search"
                    value="{{ $search ?? '' }}"
                    placeholder="Search student name or email"
                    class="w-full sm:max-w-md rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                >
                <div class="flex gap-2">
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
                        Search
                    </button>
                    @if(!empty($search))
                        <a
                            href="{{ url('/teacher/students') }}"
                            class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-300">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>
        @if($students->isEmpty())
            <div class="p-8 text-center text-gray-500">
                @if(!empty($search))
                    No students found for "{{ $search }}".
                @else
                    No students found for this school.
                @endif
            </div>
        @else
            <!-- Mobile-friendly cards -->
            <div class="md:hidden divide-y divide-gray-100 border-b border-gray-200 bg-white">
                @foreach($students as $student)
                    @php
                        $required = (float) ($student->required_training_hours ?? 0);
                        $logged = (float) ($student->logged_hours ?? 0);
                        $remaining = (float) ($student->remaining_hours ?? max($required - $logged, 0));
                        $approvedAbsentCount = (int) ($student->approved_absent_days ?? 0);
                        $meritBreakdown = $violationBreakdowns[$student->id] ?? ['undertime' => 0, 'excess_absence' => 0, 'manual' => 0, 'total' => 0];
                        $meritsCount = (int) ($meritBreakdown['total'] ?? 0);
                        $meritsAtFinal = $meritsCount >= 3;
                        $meritBtnClass = $meritsAtFinal
                            ? 'font-semibold text-red-700 hover:bg-red-50'
                            : ($meritsCount > 0 ? 'font-semibold text-amber-700 hover:bg-amber-50' : 'text-gray-500 hover:bg-gray-50');
                        $meritRingClass = $meritsAtFinal ? 'focus:ring-red-500' : 'focus:ring-amber-500';
                        $meritSubClass = $meritsAtFinal ? 'text-red-800/90' : 'text-amber-800/90';
                    @endphp
                    <div class="p-4 space-y-2">
                        <div>
                            <p class="text-sm font-medium text-gray-900 break-words">{{ $student->name }}</p>
                            <p class="text-xs text-gray-500 break-all mt-0.5">{{ $student->email }}</p>
                        </div>
                        <dl class="grid grid-cols-2 gap-x-3 gap-y-2 text-xs sm:text-sm">
                            <div>
                                <dt class="text-gray-500">Required</dt>
                                <dd class="font-medium text-gray-900">{{ number_format($required, 1) }} hrs</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">From DTR</dt>
                                <dd class="font-medium text-gray-900">{{ number_format($logged, 1) }} hrs</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Remaining</dt>
                                <dd class="font-medium {{ $remaining > 0 ? 'text-amber-700' : 'text-green-700' }}">{{ number_format($remaining, 1) }} hrs</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Est. end</dt>
                                <dd class="font-medium text-gray-900">
                                    @if($remaining <= 0 && $required > 0)
                                        <span class="text-green-700">Completed</span>
                                    @elseif(!empty($student->estimated_end_date))
                                        {{ \Carbon\Carbon::parse($student->estimated_end_date)->format('M d, Y') }}
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Merits</dt>
                                <dd>
                                    <button type="button"
                                            class="merit-details-trigger inline-flex flex-col items-start rounded-md px-1 py-0.5 -mx-1 transition-colors focus:outline-none focus:ring-2 {{ $meritRingClass }} {{ $meritBtnClass }}"
                                            data-merits-url="{{ route('user.teacher.students.merits', $student) }}"
                                            title="View merit details (read only)">
                                        <span class="tabular-nums text-sm">{{ number_format($meritsCount) }}</span>
                                        @if($meritsCount > 0)
                                            <span class="text-[10px] font-normal leading-tight {{ $meritSubClass }} tabular-nums">
                                                {{ (int) $meritBreakdown['undertime'] }}+{{ (int) $meritBreakdown['excess_absence'] }}+{{ (int) $meritBreakdown['manual'] }}
                                            </span>
                                        @endif
                                    </button>
                                </dd>
                            </div>
                            <div class="col-span-2">
                                <dt class="text-gray-500">Approved absent days</dt>
                                <dd class="{{ $approvedAbsentCount > 0 ? 'text-amber-700 font-semibold' : 'text-gray-600' }}">
                                    {{ $approvedAbsentCount }} {{ $approvedAbsentCount === 1 ? 'day' : 'days' }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                @endforeach
            </div>
            <div class="hidden md:block overflow-x-auto" style="-webkit-overflow-scrolling: touch;">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time Needed (Required)</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Time from DTR</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining Time Needed</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estimated End Date</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" title="Total merits. Click for breakdown (view only).">Merits</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approved Absent Days</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach($students as $student)
                            @php
                                $required = (float) ($student->required_training_hours ?? 0);
                                $logged = (float) ($student->logged_hours ?? 0);
                                $remaining = (float) ($student->remaining_hours ?? max($required - $logged, 0));
                                $meritBreakdown = $violationBreakdowns[$student->id] ?? ['undertime' => 0, 'excess_absence' => 0, 'manual' => 0, 'total' => 0];
                                $meritsCount = (int) ($meritBreakdown['total'] ?? 0);
                                $meritTitle = $meritsCount > 0
                                    ? 'Total '.$meritsCount.' = under-time '.(int) ($meritBreakdown['undertime'] ?? 0).' + excess '.(int) ($meritBreakdown['excess_absence'] ?? 0).' + manual '.(int) ($meritBreakdown['manual'] ?? 0)
                                    : 'No merits on record';
                                $meritsAtFinal = $meritsCount >= 3;
                                $meritBtnClass = $meritsAtFinal
                                    ? 'font-semibold text-red-700 hover:bg-red-50 hover:text-red-900'
                                    : ($meritsCount > 0 ? 'font-semibold text-amber-700 hover:bg-amber-50 hover:text-amber-900' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700');
                                $meritRingClass = $meritsAtFinal ? 'focus:ring-red-500' : 'focus:ring-amber-500';
                                $meritSubClass = $meritsAtFinal ? 'text-red-800/90' : 'text-amber-800/90';
                            @endphp
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $student->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $student->email }}</p>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ number_format($required, 1) }} hrs
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ number_format($logged, 1) }} hrs
                                </td>
                                <td class="px-4 py-3 text-sm {{ $remaining > 0 ? 'text-amber-700 font-medium' : 'text-green-700 font-medium' }}">
                                    {{ number_format($remaining, 1) }} hrs
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    @if($remaining <= 0 && $required > 0)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Completed
                                        </span>
                                    @elseif(!empty($student->estimated_end_date))
                                        {{ \Carbon\Carbon::parse($student->estimated_end_date)->format('M d, Y') }}
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center text-sm">
                                    <button type="button"
                                            class="merit-details-trigger inline-flex flex-col items-center rounded-md px-2 py-1 -mx-2 transition-colors focus:outline-none focus:ring-2 {{ $meritRingClass }} focus:ring-offset-1 {{ $meritBtnClass }}"
                                            data-merits-url="{{ route('user.teacher.students.merits', $student) }}"
                                            title="{{ $meritTitle }} — click to view details (read only)">
                                        <span class="tabular-nums">{{ number_format($meritsCount) }}</span>
                                        @if($meritsCount > 0)
                                            <span class="block text-[10px] font-normal leading-tight {{ $meritSubClass }} mt-0.5 tabular-nums">
                                                {{ (int) $meritBreakdown['undertime'] }}+{{ (int) $meritBreakdown['excess_absence'] }}+{{ (int) $meritBreakdown['manual'] }}
                                            </span>
                                        @endif
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    @php
                                        $approvedAbsentCount = (int) ($student->approved_absent_days ?? 0);
                                    @endphp
                                    <span class="{{ $approvedAbsentCount > 0 ? 'text-amber-700 font-medium' : 'text-gray-500' }}">
                                        {{ $approvedAbsentCount }}
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        {{ $approvedAbsentCount === 1 ? 'day' : 'days' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@include('partials.merit-details-readonly')
@endsection
