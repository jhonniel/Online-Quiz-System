@component('mail::message')
# Request Status Update

@php
    $statusLabel = $status === 'resubmission_requested' || ($status === 'pending' && $leaveRequest->reviewed_at) ? 'Resubmission Required' : ucfirst($status);
@endphp

Hello {{ $leaveRequest->user->name }},

Your {{ $leaveRequest->type_label }} request has been **{{ $statusLabel }}**.

## Request Details

**Request Type:** {{ $leaveRequest->type_label }}
**Start Date:** {{ $leaveRequest->start_date->format('F d, Y') }}
@if($leaveRequest->end_date && $leaveRequest->end_date->format('Y-m-d') !== $leaveRequest->start_date->format('Y-m-d'))
**End Date:** {{ $leaveRequest->end_date->format('F d, Y') }}
**Duration:** {{ $leaveRequest->days }} {{ $leaveRequest->days == 1 ? 'day' : 'days' }}
@else
**Date:** {{ $leaveRequest->start_date->format('F d, Y') }}
@endif

@if($adminNotes)
## Admin Notes

{{ $adminNotes }}
@endif

@if($status === 'approved')
✅ Your {{ $leaveRequest->type_label }} request has been **{{ $statusLabel }}**. Please make sure to coordinate with your team regarding your absence.
@elseif($status === 'rejected')
❌ Your {{ $leaveRequest->type_label }} request has been **{{ $statusLabel }}**. If you have any questions, please contact your supervisor or HR.
@elseif($status === 'resubmission_requested' || ($status === 'pending' && $leaveRequest->reviewed_at))
⚠️ Your {{ $leaveRequest->type_label }} request has been **{{ $statusLabel }}**. Please review the admin notes above and make the necessary corrections. You can edit your request from your leave requests page.
@endif

@component('mail::button', ['url' => route('user.leave-requests.show', $leaveRequest)])
View Leave Request Details
@endcomponent

**Request ID:** #{{ $leaveRequest->id }}
**Submitted:** {{ $leaveRequest->created_at->format('F j, Y \a\t g:i A') }}
**Status Updated:** {{ $leaveRequest->reviewed_at ? $leaveRequest->reviewed_at->format('F j, Y \a\t g:i A') : now()->format('F j, Y \a\t g:i A') }}

Thanks,<br>
Infosoft-Studio
@endcomponent

