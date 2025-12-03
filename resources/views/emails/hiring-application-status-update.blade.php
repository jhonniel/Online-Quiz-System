<x-mail::message>
# Application Status Update

Hello {{ $application->first_name }},

@if($status === 'accepted')
# 🎉 Congratulations!

Your application for the **{{ $position->title ?? $application->position_applied }}** position has been **accepted**!

@if($application->acceptance_token)
**Please proceed** to create your account and continue with the next steps in the hiring process.

<x-mail::button :url="route('hiring.accept', $application->acceptance_token)">
Proceed to Create Account
</x-mail::button>
@else
**Please proceed** with the next steps as instructed by our team.
@endif

@elseif($status === 'rejected')
# Application Update

We regret to inform you that your application for the **{{ $position->title ?? $application->position_applied }}** position has been **rejected** at this time.

@else
# Application Status: {{ ucfirst($status) }}

Your application for the **{{ $position->title ?? $application->position_applied }}** position status has been updated to **{{ ucfirst($status) }}**.

@endif

@if($message)
## Additional Information

{{ $message }}
@endif

Thank you for your interest in joining our team.

Best regards,<br>
Infosoft-Studio
</x-mail::message>
