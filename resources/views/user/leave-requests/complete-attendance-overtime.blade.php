@extends('layouts.user')

@section('content')
@php
    $lockedStart = $formData['start_date'];
    $lockedEnd = $formData['end_date'];
    $lockedDates = $formData['overtime_specific_dates'];
    $startLabel = \Carbon\Carbon::parse($lockedStart)->format('M d, Y');
    $endLabel = \Carbon\Carbon::parse($lockedEnd)->format('M d, Y');
@endphp
<div class="h-full flex flex-col min-h-0">
    <div class="bg-gradient-to-r from-orange-500 to-orange-600 shadow-sm p-4 flex-shrink-0">
        <div class="flex items-center justify-between">
            <div class="ml-1">
                <h1 class="text-lg sm:text-xl font-bold text-white">Complete Additional Time Request</h1>
                <p class="text-orange-100 text-sm">Record Attendance — add reason, tasks, and documents</p>
            </div>
            <a href="{{ route('user.leave-requests.show', $leaveRequest) }}"
               class="inline-flex items-center px-4 py-2 bg-white/20 border border-white/30 rounded-lg text-white hover:bg-white/30 text-sm">
                Back
            </a>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto p-4">
        <div class="max-w-3xl mx-auto space-y-4">
            @if(session('info'))
                <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">{{ session('info') }}</div>
            @endif

            <div class="rounded-lg border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-orange-900">
                <p class="font-semibold">Additional Time hours were recorded from Record Attendance.</p>
                <p class="mt-1">Submit a reason below so your request can be reviewed. ClickUp links and supporting documents are optional. Admin approval is blocked until this form is completed.</p>
            </div>

            <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                <form action="{{ route('user.leave-requests.complete-attendance-overtime.update', $leaveRequest) }}"
                      method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="type" value="overtime">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                            <div class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 text-gray-800 text-sm">
                                {{ $startLabel }}
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Set from Record Attendance (cannot be changed).</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                            <div class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 text-gray-800 text-sm">
                                {{ $endLabel }}
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Set from Record Attendance (cannot be changed).</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Additional Time date(s) from Record Attendance</label>
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                            <ul class="space-y-1 text-sm text-gray-800">
                                @foreach($lockedDates as $date)
                                    <li class="font-mono">{{ \Carbon\Carbon::parse($date)->format('M d, Y') }} <span class="text-gray-500">({{ $date }})</span></li>
                                    <input type="hidden" name="overtime_specific_dates[]" value="{{ $date }}">
                                @endforeach
                            </ul>
                        </div>
                        @error('overtime_specific_dates')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        @error('overtime_specific_dates.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="overtime_hours" class="block text-sm font-medium text-gray-700 mb-2">
                            Total Additional Time Hours (HH:MM) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="overtime_hours" id="overtime_hours" required readonly
                               value="{{ old('overtime_hours', $formData['overtime_hours']) }}"
                               class="time-input w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                        <p class="mt-1 text-xs text-gray-500">Fixed from Record Attendance (cannot be changed here).</p>
                        @error('overtime_hours')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Reason for Additional Time <span class="text-red-500">*</span>
                        </label>
                        <textarea name="reason" id="reason" rows="3" required
                                  placeholder="e.g., urgent project deadline, critical system maintenance, staffing shortage"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">{{ old('reason') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Explain why the extra hours were needed (who approved the additional time can be noted here).</p>
                        @error('reason')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="overtime_tasks" class="block text-sm font-medium text-gray-700 mb-2">
                            Tasks / ClickUp Links <span class="text-gray-400">(Optional)</span>
                        </label>
                        <textarea name="overtime_tasks" id="overtime_tasks" rows="4"
                                  placeholder="https://app.clickup.com/t/... (optional)"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">{{ old('overtime_tasks') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">If provided, enter ClickUp links only (URLs separated by spaces or new lines).</p>
                        @error('overtime_tasks')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="supporting_documents_input" class="block text-sm font-medium text-gray-700 mb-2">
                            Supporting Documents <span class="text-gray-400">(Optional)</span>
                        </label>
                        <input type="file" name="supporting_documents[]" id="supporting_documents_input"
                               accept=".pdf,.jpg,.jpeg,.png" multiple
                               class="w-full text-sm text-gray-600">
                        <p class="mt-1 text-xs text-gray-500">Optional — upload up to 5 files (PDF/JPG/PNG), 5MB max each.</p>
                        @error('supporting_documents')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        @error('supporting_documents.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <a href="{{ route('user.leave-requests.index') }}"
                           class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Later</a>
                        <button type="submit"
                                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
                            Submit for approval
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
