@foreach($payslips as $payslip)
    <tr class="hover:bg-gray-50">
        <td class="px-4 py-3">
            <input type="checkbox" class="payslip-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" value="{{ $payslip->id }}" aria-label="Select {{ $payslip->employee_name }}">
        </td>
        <td class="px-4 py-3 text-sm">
            <div class="font-medium text-gray-900">{{ $payslip->employee_name }}</div>
            @if($payslip->employee)
                <div class="text-gray-500">{{ $payslip->employee->email }}</div>
                @if($payslip->employee->department)
                    <div class="text-xs text-gray-400 mt-0.5">{{ $payslip->employee->department->name }}</div>
                @endif
            @else
                <span class="inline-flex items-center gap-1 mt-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-800 ring-1 ring-amber-600/15">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    Unlinked
                </span>
                @if($payslip->employee_email)
                    <div class="text-xs text-gray-400 mt-1">CSV: {{ $payslip->employee_email }}</div>
                @endif
            @endif
        </td>
        <td class="px-4 py-3 text-sm text-gray-700">{{ $payslip->formatMoney($payslip->gross_pay) }}</td>
        <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $payslip->formatMoney($payslip->net_pay) }}</td>
        <td class="px-4 py-3 text-sm text-gray-500">{{ $payslip->created_at?->format('M d, Y') }}</td>
        <td class="px-4 py-3 text-sm text-right space-x-2 whitespace-nowrap">
            @if(!$payslip->employee)
                <button type="button"
                        @click="openLinkModal(@js(route('admin.payslip.link', $payslip)), @js($payslip->employee_name), @js($payslip->employee_email), @js($payslip->periodLabel()))"
                        class="inline-flex items-center gap-1 text-amber-700 hover:text-amber-900 font-medium">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    Link
                </button>
            @endif
            <a href="{{ route('admin.payslip.show', $payslip) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">View</a>
            <form action="{{ route('admin.payslip.destroy', $payslip) }}" method="POST" class="inline" onsubmit="return confirm('Delete this payslip?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Delete</button>
            </form>
        </td>
    </tr>
@endforeach
