<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\EmployeePayslip;
use Illuminate\Http\Request;

class PayslipController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user->role === 'employee', 403);

        $payslips = EmployeePayslip::query()
            ->where('user_id', $user->id)
            ->orderByDesc('period_end')
            ->orderByDesc('id')
            ->paginate(10);

        return view('user.payslips.index', compact('payslips'));
    }

    public function show(Request $request, EmployeePayslip $payslip)
    {
        $user = $request->user();
        abort_unless($user->role === 'employee', 403);
        abort_unless((int) $payslip->user_id === (int) $user->id, 403);

        return view('user.payslips.show', compact('payslip'));
    }
}
