@extends('layouts.user')

@section('content')
<div class="space-y-4 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-4">
        <h1 class="text-xl font-bold text-white">My Payslips</h1>
        <p class="text-indigo-100 text-sm mt-1">View your uploaded payslip records.</p>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Period</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Gross Pay</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Net Pay</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($payslips as $payslip)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $payslip->periodLabel() }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $payslip->formatMoney($payslip->gross_pay) }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $payslip->formatMoney($payslip->net_pay) }}</td>
                            <td class="px-4 py-3 text-sm text-right">
                                <a href="{{ route('user.payslips.show', $payslip) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">No payslips available yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payslips->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">{{ $payslips->links() }}</div>
        @endif
    </div>
</div>
@endsection
