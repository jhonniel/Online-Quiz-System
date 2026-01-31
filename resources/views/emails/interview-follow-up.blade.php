@component('mail::message')
# Interview Follow-Up

Hello {{ $application->first_name }},

We hope this message finds you well. This is a follow-up regarding your scheduled interview for the **{{ $position ? $position->title : ($application->position_applied ?? 'position') }}** position.

## Interview Details

**Date & Time:** {{ $interviewDate->format('F j, Y \a\t g:i A') }}

@if($application->interview_date)
**Original Schedule:** {{ $application->interview_date->format('F j, Y \a\t g:i A') }}
@endif

---

## Need to Reschedule?

If you need to reschedule your interview, please contact us through our social media channels:

@if($socialMediaLink)
**Contact us here:** [{{ $socialMediaLink }}]({{ $socialMediaLink }})
@else
Please contact us through our official social media channels to discuss rescheduling options.
@endif

We understand that circumstances can change, and we're happy to work with you to find a time that works for both parties.

---

## What to Expect

Please come prepared for the interview. If you have any questions before the interview, feel free to reach out to us.

We look forward to meeting with you!

Best regards,<br>
{{ config('app.name') }}

@endcomponent
