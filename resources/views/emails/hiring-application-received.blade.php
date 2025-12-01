<x-mail::message>
# New Hiring Application Received

Hello,

A new hiring application has been submitted for the **{{ $position->title ?? $application->position_applied }}** position.

## Applicant Information

- **Name:** {{ $application->first_name }} {{ $application->last_name }}
- **Email:** {{ $application->email }}
- **Phone:** {{ $application->phone }}
@if($application->address)
- **Address:** {{ $application->address }}
@endif
@if($application->birth_date)
- **Date of Birth:** {{ $application->birth_date->format('F j, Y') }}
@endif

@if($application->cover_letter)
## Cover Letter

{{ $application->cover_letter }}
@endif

@if($application->resume_link)
## Resume

[View Resume]({{ $application->resume_link }})
@endif

<x-mail::button :url="route('admin.hiring-applications.show', $application->id)">
View Application Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
