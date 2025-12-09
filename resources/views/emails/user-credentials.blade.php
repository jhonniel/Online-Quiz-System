<x-mail::message>
# Your Account Credentials

Hello {{ $user->name }},

Your account has been created in our system. Below are your login credentials:

## Your Account Credentials

**Email:** {{ $email }}

**Password:** {{ $password }}

<x-mail::button :url="$loginUrl">
Login to Your Account
</x-mail::button>

## Important Notes

- Please keep your credentials secure and do not share them with anyone.
- You can change your password after logging in.
- Use the login button above or visit: {{ $loginUrl }}

If you have any questions or need assistance, please contact the administrator.

Best regards,<br>
{{ config('app.name') }}
</x-mail::message>

