<x-mail::message>
@if($isReschedule)
# 📅 Interview Rescheduled

Hello {{ $application->first_name }},

Your interview for the **{{ $position->title ?? $application->position_applied }}** position has been **rescheduled**.
@else
# 📅 Interview Scheduled

Hello {{ $application->first_name }},

Your interview for the **{{ $position->title ?? $application->position_applied }}** position has been **scheduled**.
@endif

## Interview Details

**Date & Time:** {{ \Carbon\Carbon::parse($interviewDate)->format('F j, Y g:i A') }}

@if($adminNotes)
## Additional Information

{{ $adminNotes }}
@endif

@if($isReschedule)
Please note the new interview date above. If you have any questions or concerns, please contact us.
@else
Please make sure to be available on this date. We will contact you with further details about the interview location and time.
@endif

Thank you for your interest in joining our team. We look forward to meeting you!

Best regards,<br>
Infosoft-Studio
</x-mail::message>



