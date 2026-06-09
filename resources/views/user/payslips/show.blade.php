@extends('layouts.user')

@section('content')
<div class="space-y-4 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('user.payslips.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to payslips</a>
    </div>
    @include('admin.employee-management.payslip.partials.sheet', ['payslip' => $payslip])
</div>
@endsection
