@component('mail::message')
# New Request Submitted

A new {{ $leaveRequest->type_label }} request has been submitted and requires your review.

## Employee Information

**Name:** {{ $leaveRequest->user->name }}
**Email:** {{ $leaveRequest->user->email }}

## Request Details

**Request Type:** {{ $leaveRequest->type_label }}
**Start Date:** {{ $leaveRequest->start_date->format('F d, Y') }}
@if($leaveRequest->end_date && $leaveRequest->end_date->format('Y-m-d') !== $leaveRequest->start_date->format('Y-m-d'))
**End Date:** {{ $leaveRequest->end_date->format('F d, Y') }}
**Duration:** {{ $leaveRequest->days }} {{ $leaveRequest->days == 1 ? 'day' : 'days' }}
@else
**Date:** {{ $leaveRequest->start_date->format('F d, Y') }}
@endif

**Status:** Pending Review

@if($leaveRequest->reason)
## Reason

{{ $leaveRequest->reason }}
@endif

@component('mail::button', ['url' => route('admin.leave-requests.show', $leaveRequest)])
Review Leave Request
@endcomponent

**Request ID:** #{{ $leaveRequest->id }}
**Submitted:** {{ $leaveRequest->created_at->format('F j, Y \a\t g:i A') }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent

