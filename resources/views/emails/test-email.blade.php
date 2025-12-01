@component('mail::message')
# Test Email

This is a test email from your **{{ config('app.name') }}** system.

If you received this email, it means your email configuration is working correctly! ✅

**Email Configuration Details:**
- Mail Driver: {{ config('mail.default') }}
- From Address: {{ config('mail.from.address') }}
- From Name: {{ config('mail.from.name') }}

**Sent at:** {{ now()->format('F j, Y \a\t g:i A') }}

---

This is an automated test email. No action is required.

Thanks,<br>
{{ config('app.name') }} System
@endcomponent

