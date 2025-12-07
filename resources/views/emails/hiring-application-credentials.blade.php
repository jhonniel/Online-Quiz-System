<x-mail::message>
# 🎉 Congratulations! Your Application Has Been Accepted

Hello {{ $application->first_name }},

We are pleased to inform you that your application for the **{{ $position->title ?? $application->position_applied }}** position has been **accepted**!

## Your Account Credentials

Your account has been automatically created and activated. You can now log in to view the status of your application and proceed with the next steps.

**Email:** {{ $email }}

**Password:** {{ $password }}

<x-mail::button :url="$loginUrl">
Login to Your Account
</x-mail::button>

@if($interviewDate)
## Interview Scheduled

Your interview has been scheduled for:

**Date:** {{ \Carbon\Carbon::parse($interviewDate)->format('F j, Y') }}

Please make sure to be available on this date. We will contact you with further details about the interview location and time.
@endif

## Important Notes

- Please keep your credentials secure and do not share them with anyone.
- You can change your password after logging in.
- Your account is already active, so you can log in immediately.
- Use the login button above or visit our website to access your account.

Thank you for your interest in joining our team. We look forward to working with you!

Best regards,<br>
Infosoft-Studio
</x-mail::message>

