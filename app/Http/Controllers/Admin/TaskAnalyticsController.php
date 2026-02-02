<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TaskAnalyticsController extends Controller
{
    public function dashboard(Request $request)
    {
        // Check permission - only super admins or users with task_analytics permission
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Access denied. Only super administrators can view task analytics.');
        }

        // Get filter parameters
        $dateFrom = $request->input('date_from', Carbon::now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));
        $taskType = $request->input('type', 'all'); // all, personal, group
        $statusFilter = $request->input('status', 'all'); // all, todo, in_progress, done
        $search = $request->input('search', '');

        // Parse dates
        try {
            $startDate = Carbon::parse($dateFrom)->startOfDay();
            $endDate = Carbon::parse($dateTo)->endOfDay();
        } catch (\Exception $e) {
            $startDate = Carbon::now()->subDays(30)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        }

        // Get all tasks with relationships
        $tasksQuery = Task::with(['creator', 'assignments.user', 'taskList', 'parent'])
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        if ($taskType !== 'all') {
            $tasksQuery->where('type', $taskType);
        }

        if ($statusFilter !== 'all') {
            $tasksQuery->where('status', $statusFilter);
        }

        if ($search) {
            $tasksQuery->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('creator', function($query) use ($search) {
                      $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $allTasks = $tasksQuery->orderBy('created_at', 'desc')->paginate(20);

        // Calculate statistics
        $totalTasksQuery = Task::query();
        if ($taskType !== 'all') {
            $totalTasksQuery->where('type', $taskType);
        }
        $totalTasks = $totalTasksQuery->whereBetween('created_at', [$startDate, $endDate])->get();

        $stats = [
            'total_tasks' => $totalTasks->count(),
            'personal_tasks' => $totalTasks->where('type', 'personal')->count(),
            'group_tasks' => $totalTasks->where('type', 'group')->count(),
            'completed_tasks' => $totalTasks->where('status', 'done')->count(),
            'in_progress_tasks' => $totalTasks->where('status', 'in_progress')->count(),
            'todo_tasks' => $totalTasks->where('status', 'todo')->count(),
        ];

        // Calculate fastest task completers
        $fastestCompleters = $this->calculateFastestCompleters($startDate, $endDate, $taskType);

        // Task status distribution
        $statusDistribution = [
            'todo' => $totalTasks->where('status', 'todo')->count(),
            'in_progress' => $totalTasks->where('status', 'in_progress')->count(),
            'done' => $totalTasks->where('status', 'done')->count(),
        ];

        // Add any custom statuses
        $allStatuses = $totalTasks->pluck('status')->unique()->filter();
        foreach ($allStatuses as $status) {
            if (!isset($statusDistribution[$status])) {
                $statusDistribution[$status] = $totalTasks->where('status', $status)->count();
            }
        }

        // Task type distribution
        $typeDistribution = [
            'personal' => $totalTasks->where('type', 'personal')->count(),
            'group' => $totalTasks->where('type', 'group')->count(),
        ];

        // Task creation trends (daily)
        $creationTrends = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $tasksOnDate = $totalTasks->filter(function($task) use ($currentDate) {
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
        $tasksByUser = $totalTasks->groupBy('created_by')
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

        return view('admin.tasks.analytics.dashboard', compact(
            'allTasks',
            'stats',
            'statusDistribution',
            'typeDistribution',
            'creationTrends',
            'completionRate',
            'fastestCompleters',
            'tasksByUser',
            'dateFrom',
            'dateTo',
            'taskType',
            'statusFilter',
            'search'
        ));
    }

    /**
     * Calculate fastest task completers
     */
    private function calculateFastestCompleters($startDate, $endDate, $taskType = 'all')
    {
        // Get completed tasks
        $completedTasksQuery = Task::where('status', 'done')
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        if ($taskType !== 'all') {
            $completedTasksQuery->where('type', $taskType);
        }

        $completedTasks = $completedTasksQuery->with(['creator'])->get();

        // Calculate completion time for each task
        $userCompletionTimes = [];
        
        foreach ($completedTasks as $task) {
            // Find when task was marked as done from activity logs
            $doneLog = TaskActivityLog::where('task_id', $task->id)
                ->where('action', 'status_changed')
                ->where('new_value', 'done')
                ->orderBy('created_at', 'asc')
                ->first();

            // If no activity log, use updated_at as fallback (when status was changed to done)
            if (!$doneLog) {
                // Use updated_at as fallback (when status was changed to done)
                $completionTime = $task->created_at->diffInHours($task->updated_at);
            } else {
                $completionTime = $task->created_at->diffInHours($doneLog->created_at);
            }
            
            // Only count tasks that were completed (completion time > 0)
            if ($completionTime >= 0) {
                // Get the user who created the task (or completed it)
                $userId = $task->created_by;
                
                if (!isset($userCompletionTimes[$userId])) {
                    $userCompletionTimes[$userId] = [
                        'user_id' => $userId,
                        'tasks' => [],
                        'total_time' => 0,
                        'count' => 0,
                    ];
                }
                
                $userCompletionTimes[$userId]['tasks'][] = [
                    'task_id' => $task->id,
                    'task_title' => $task->title,
                    'completion_time_hours' => $completionTime,
                ];
                
                $userCompletionTimes[$userId]['total_time'] += $completionTime;
                $userCompletionTimes[$userId]['count']++;
            }
        }

        // Calculate averages and format
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
                'tasks' => $data['tasks'],
            ];
        })
        ->filter()
        ->sortBy('average_hours')
        ->take(10)
        ->values();

        return $fastestCompleters;
    }
}
