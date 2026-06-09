@extends('layouts.admin')

@section('title', 'Payslip')

@section('page-title', 'Payslip')

@section('breadcrumb')
<li class="flex items-center">
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    <a href="{{ route('admin.payslip.index') }}" class="text-gray-500 text-sm hover:text-gray-700">Payslip</a>
</li>
<li class="flex items-center">
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    <span class="text-gray-900 font-medium text-sm">{{ $payslip->employee_name }}</span>
</li>
@endsection

@section('content')
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <a href="{{ route('admin.payslip.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to payslips</a>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">{{ session('error') }}</div>
    @endif

    @if(!$payslip->employee)
        <div class="rounded-2xl border border-amber-200 bg-white shadow-sm overflow-hidden">
            <div class="px-5 sm:px-6 py-4 border-b border-amber-100 bg-amber-50/80">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Link to employee account</h2>
                        <p class="mt-1 text-sm text-gray-600">This payslip is not linked yet. The employee will not see it in their portal until you connect it to their account.</p>
                        <div class="mt-3 flex flex-wrap gap-2 text-xs">
                            <span class="inline-flex items-center rounded-full bg-white px-2.5 py-1 font-medium text-gray-700 ring-1 ring-gray-200">{{ $payslip->employee_name }}</span>
                            @if($payslip->employee_email)
                                <span class="inline-flex items-center rounded-full bg-white px-2.5 py-1 font-medium text-gray-700 ring-1 ring-gray-200">{{ $payslip->employee_email }}</span>
                            @endif
                            <span class="inline-flex items-center rounded-full bg-white px-2.5 py-1 font-medium text-gray-700 ring-1 ring-gray-200">{{ $payslip->periodLabel() }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-5 sm:px-6 py-5">
                @include('admin.employee-management.payslip.partials.link-form', ['payslip' => $payslip, 'employees' => $employees])
            </div>
        </div>
    @endif

    @include('admin.employee-management.payslip.partials.sheet', ['payslip' => $payslip])
</div>
@endsection
