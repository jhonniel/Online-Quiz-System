@extends('layouts.user')

@section('content')
<div class="h-full flex flex-col min-h-0">
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 shadow-sm p-4 flex-shrink-0">
        <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white">My Students</h1>
        <p class="text-indigo-100 text-sm">
            @if($schoolName)
                Students from {{ $schoolName }}
            @else
                Assign a school to this teacher account to view students
            @endif
        </p>
    </div>

    <div class="bg-white border-t border-gray-200 flex-1 min-h-0 overflow-auto">
        <div class="p-4 border-b border-gray-100 bg-gray-50">
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
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time Needed (Required)</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Time from DTR</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining Time Needed</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estimated End Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach($students as $student)
                            @php
                                $required = (float) ($student->required_training_hours ?? 0);
                                $logged = (float) ($student->logged_hours ?? 0);
                                $remaining = (float) ($student->remaining_hours ?? max($required - $logged, 0));
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
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
