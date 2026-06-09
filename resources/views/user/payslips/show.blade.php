@extends('layouts.user')

@section('content')
@include('admin.employee-management.payslip.partials.print-styles')

<div class="space-y-4 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
    <a href="{{ route('user.payslips.index') }}" class="print:hidden text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to payslips</a>
    <div class="flex flex-col items-center gap-3 sm:flex-row sm:items-start sm:justify-center">
        <div class="payslip-print-sheet payslip-print-sheet--single w-full max-w-3xl">
            <div class="payslip-print-slot">
                <div id="payslip-print-area" class="payslip-print-area w-full">
                    @include('admin.employee-management.payslip.partials.sheet', ['payslip' => $payslip])
                </div>
            </div>
        </div>
        <div class="print:hidden shrink-0 sm:pt-2">
            @include('admin.employee-management.payslip.partials.print-toolbar')
        </div>
    </div>
</div>
@endsection
