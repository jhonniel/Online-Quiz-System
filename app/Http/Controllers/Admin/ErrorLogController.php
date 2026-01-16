<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ErrorLog;
use Illuminate\Http\Request;

class ErrorLogController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = ErrorLog::with('user')->orderByDesc('created_at');

            if ($request->filled('status_code')) {
                $query->where('status_code', $request->status_code);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('message', 'like', "%{$search}%")
                        ->orWhere('path', 'like', "%{$search}%")
                        ->orWhere('exception_class', 'like', "%{$search}%");
                });
            }

            $logs = $query->paginate(20)->withQueryString();

            $stats = [
                'total' => ErrorLog::count(),
                'today' => ErrorLog::whereDate('created_at', now()->toDateString())->count(),
                'by_status' => ErrorLog::selectRaw('status_code, COUNT(*) as count')
                    ->groupBy('status_code')
                    ->orderByDesc('count')
                    ->limit(5)
                    ->get(),
            ];

            return view('admin.analytics.error-logs', compact('logs', 'stats'));
        } catch (\Exception $e) {
            \Log::error('Error in ErrorLogController::index: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.analytics.index')
                ->with('error', 'An error occurred while loading error logs. Please try again.');
        }
    }
}


