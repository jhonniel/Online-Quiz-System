<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TaskAnalyticsController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = auth()->user();
        
        // Only super admins (admins with full access) can view Task Analytics
        if (!$user->isSuperAdmin()) {
            abort(403, 'Access denied. Only administrators with full access can view Task Analytics.');
        }
        
        // Get filter parameters
        $dateFrom = $request->input('date_from', Carbon::now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));
        $taskType = $request->input('type', 'all'); // all, personal, group

        // Parse dates
        try {
            $startDate = Carbon::parse($dateFrom)->startOfDay();
            $endDate = Carbon::parse($dateTo)->endOfDay();
        } catch (\Exception $e) {
            $startDate = Carbon::now()->subDays(30)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        }

        // Get all tasks with relationships
        $tasksQuery = Task::with(['creator', 'assignments.user', 'taskList'])
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        if ($taskType !== 'all') {
            $tasksQuery->where('type', $taskType);
        }

        $allTasks = $tasksQuery->get();

        // Calculate statistics
        $stats = [
            'total_tasks' => $allTasks->count(),
            'personal_tasks' => $allTasks->where('type', 'personal')->count(),
            'group_tasks' => $allTasks->where('type', 'group')->count(),
            'completed_tasks' => $allTasks->where('status', 'done')->count(),
            'in_progress_tasks' => $allTasks->where('status', 'in_progress')->count(),
            'todo_tasks' => $allTasks->where('status', 'todo')->count(),
        ];

        // Task status distribution
        $statusDistribution = [
            'todo' => $allTasks->where('status', 'todo')->count(),
            'in_progress' => $allTasks->where('status', 'in_progress')->count(),
            'done' => $allTasks->where('status', 'done')->count(),
        ];

        // Task type distribution
        $typeDistribution = [
            'personal' => $allTasks->where('type', 'personal')->count(),
            'group' => $allTasks->where('type', 'group')->count(),
        ];

        // Task creation trends (daily)
        $creationTrends = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $tasksOnDate = $allTasks->filter(function($task) use ($currentDate) {
                return $task->created_at->isSameDay($currentDate);
            });
            
            $creationTrends[$dateKey] = [
                'date' => $currentDate->format('M d'),
                'total' => $tasksOnDate->count(),
                'completed' => $tasksOnDate->where('status', 'done')->count(),
                'in_progress' => $tasksOnDate->where('status', 'in_progress')->count(),
                'todo' => $tasksOnDate->where('status', 'todo')->count(),
            ];
            $currentDate->addDay();
        }

        // Completion rate over time
        $completionRate = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $tasksOnDate = Task::where('created_at', '<=', $currentDate->endOfDay())
                ->when($taskType !== 'all', function($q) use ($taskType) {
                    $q->where('type', $taskType);
                })
                ->get();
            
            $total = $tasksOnDate->count();
            $completed = $tasksOnDate->where('status', 'done')->count();
            
            $completionRate[$dateKey] = [
                'date' => $currentDate->format('M d'),
                'total' => $total,
                'completed' => $completed,
                'rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            ];
            $currentDate->addDay();
        }

        // Tasks by user (who created most tasks)
        $tasksByUser = $allTasks->groupBy('created_by')
            ->map(function($userTasks, $userId) {
                $user = User::find($userId);
                return [
                    'user' => $user,
                    'total' => $userTasks->count(),
                    'completed' => $userTasks->where('status', 'done')->count(),
                    'in_progress' => $userTasks->where('status', 'in_progress')->count(),
                    'todo' => $userTasks->where('status', 'todo')->count(),
                ];
            })
            ->sortByDesc('total')
            ->take(10)
            ->values();

        // Fastest task completers
        $fastestCompleters = $this->calculateFastestCompleters($startDate, $endDate, $taskType);

        // Priority distribution
        $priorityDistribution = [
            'high' => $allTasks->where('priority', 'high')->count(),
            'medium' => $allTasks->where('priority', 'medium')->count(),
            'low' => $allTasks->where('priority', 'low')->count(),
        ];

        // Tasks by day of week
        $tasksByDayOfWeek = [];
        foreach ($allTasks as $task) {
            $dayName = $task->created_at->format('l');
            if (!isset($tasksByDayOfWeek[$dayName])) {
                $tasksByDayOfWeek[$dayName] = 0;
            }
            $tasksByDayOfWeek[$dayName]++;
        }

        return view('admin.tasks.analytics', compact(
            'stats',
            'statusDistribution',
            'typeDistribution',
            'creationTrends',
            'completionRate',
            'fastestCompleters',
            'tasksByUser',
            'priorityDistribution',
            'tasksByDayOfWeek',
            'dateFrom',
            'dateTo',
            'taskType'
        ));
    }

    /**
     * Calculate fastest task completers
     */
    private function calculateFastestCompleters($startDate, $endDate, $taskType = 'all')
    {
        $completedTasksQuery = Task::where('status', 'done')
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        if ($taskType !== 'all') {
            $completedTasksQuery->where('type', $taskType);
        }

        $completedTasks = $completedTasksQuery->with(['creator'])->get();

        $userCompletionTimes = [];
        
        foreach ($completedTasks as $task) {
            $completionTime = $task->created_at->diffInHours($task->updated_at);
            
            if ($completionTime >= 0) {
                $userId = $task->created_by;
                
                if (!isset($userCompletionTimes[$userId])) {
                    $userCompletionTimes[$userId] = [
                        'user_id' => $userId,
                        'total_time' => 0,
                        'count' => 0,
                    ];
                }
                
                $userCompletionTimes[$userId]['total_time'] += $completionTime;
                $userCompletionTimes[$userId]['count']++;
            }
        }

        $fastestCompleters = collect($userCompletionTimes)->map(function($data) {
            $user = User::find($data['user_id']);
            if (!$user) {
                return null;
            }

            $avgTime = $data['count'] > 0 ? round($data['total_time'] / $data['count'], 2) : 0;
            
            return [
                'user' => $user,
                'total_completed' => $data['count'],
                'average_hours' => $avgTime,
                'total_hours' => round($data['total_time'], 2),
                'average_days' => round($avgTime / 24, 2),
            ];
        })
        ->filter()
        ->sortBy('average_hours')
        ->take(10)
        ->values();

        return $fastestCompleters;
    }
}
