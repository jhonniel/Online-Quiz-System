@component('mail::message')
# Leave Request Status Update

Hello {{ $leaveRequest->user->name }},

Your leave request has been **{{ ucfirst($status) }}@if($status === 'pending' && $leaveRequest->reviewed_at) (Resubmission Required)@endif**.

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
✅ Your leave request has been **approved**. Please make sure to coordinate with your team regarding your absence.
@elseif($status === 'rejected')
❌ Your leave request has been **rejected**. If you have any questions, please contact your supervisor or HR.
@elseif($status === 'pending' && $leaveRequest->reviewed_at)
⚠️ Your leave request requires **resubmission**. Please review the admin notes above and make the necessary corrections. You can edit your request from your leave requests page.
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

