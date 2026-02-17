<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\TaskComment;
use App\Models\TaskAttachment;
use App\Models\CustomBoard;
use App\Models\TaskList;
use App\Models\TaskListInvitation;
use App\Models\TaskActivityLog;
use App\Models\CustomPriority;
use App\Models\TaskInvitation;
use App\Models\User;
use App\Mail\TaskListInvitation as TaskListInvitationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    /**
     * Display Task Dashboard - All tasks for admins
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        
        // Only admins can access this dashboard
        if (!$user->isSuperAdmin()) {
            abort(403, 'Access denied. Only administrators with full access can view the Task Dashboard.');
        }
        
        $view = $request->get('view', 'board'); // 'board' or 'list'
        
        // Get ALL tasks (both personal and group) for admin view
        $tasksQuery = Task::whereNull('parent_id') // Only show parent tasks
            ->with([
                'creator',
                'assignments.user',
                'attachments',
                'comments.user',
                'subtasks',
                'taskList'
            ])
            ->orderBy('created_at', 'desc');
        
        // Apply filters if provided
        $statusFilter = $request->get('status');
        if ($statusFilter) {
            $tasksQuery->where('status', $statusFilter);
        }
        
        $typeFilter = $request->get('type');
        if ($typeFilter && $typeFilter !== 'all') {
            $tasksQuery->where('type', $typeFilter);
        }
        
        $tasks = $tasksQuery->get();
        
        // Group tasks by status for board view
        $tasksByStatus = [
            'todo' => $tasks->where('status', 'todo'),
            'in_progress' => $tasks->where('status', 'in_progress'),
            'done' => $tasks->where('status', 'done'),
        ];
        
        // Calculate chart data
        // Task status distribution
        $statusDistribution = [
            'todo' => $tasks->where('status', 'todo')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'done' => $tasks->where('status', 'done')->count(),
        ];
        
        // Task type distribution
        $typeDistribution = [
            'personal' => $tasks->where('type', 'personal')->count(),
            'group' => $tasks->where('type', 'group')->count(),
        ];
        
        // Task creation trends (last 30 days)
        $creationTrends = [];
        $startDate = \Carbon\Carbon::now()->subDays(30);
        $endDate = \Carbon\Carbon::now();
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $tasksOnDate = $tasks->filter(function($task) use ($currentDate) {
                return $task->created_at && $task->created_at->isSameDay($currentDate);
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
        
        // Completion rate over time (last 30 days)
        $completionRate = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $tasksUpToDate = Task::whereNull('parent_id')
                ->where('created_at', '<=', $currentDate->endOfDay())
                ->when($typeFilter && $typeFilter !== 'all', function($q) use ($typeFilter) {
                    $q->where('type', $typeFilter);
                })
                ->get();
            
            $total = $tasksUpToDate->count();
            $completed = $tasksUpToDate->where('status', 'done')->count();
            
            $completionRate[$dateKey] = [
                'date' => $currentDate->format('M d'),
                'total' => $total,
                'completed' => $completed,
                'rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            ];
            $currentDate->addDay();
        }
        
        // Priority distribution
        $priorityDistribution = [
            'high' => $tasks->where('priority', 'high')->count(),
            'medium' => $tasks->where('priority', 'medium')->count(),
            'low' => $tasks->where('priority', 'low')->count(),
        ];
        
        // Tasks by user (top creators)
        $tasksByUser = $tasks->groupBy('created_by')
            ->map(function($userTasks, $userId) {
                $user = User::find($userId);
                if (!$user) return null;
                return [
                    'user' => $user,
                    'total' => $userTasks->count(),
                    'completed' => $userTasks->where('status', 'done')->count(),
                ];
            })
            ->filter()
            ->sortByDesc('total')
            ->take(10)
            ->values();
        
        // Users with most completed tasks (done tasks)
        $usersWithMostCompleted = Task::whereNull('parent_id')
            ->where('status', 'done')
            ->when($typeFilter && $typeFilter !== 'all', function($q) use ($typeFilter) {
                $q->where('type', $typeFilter);
            })
            ->with('creator')
            ->get()
            ->groupBy('created_by')
            ->map(function($userTasks, $userId) {
                $user = User::find($userId);
                if (!$user) return null;
                return [
                    'user' => $user,
                    'completed_count' => $userTasks->count(),
                ];
            })
            ->filter()
            ->sortByDesc('completed_count')
            ->take(10)
            ->values();
        
        // Task changes/updates over time (last 30 days)
        $taskChangesOverTime = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $changesOnDate = \App\Models\TaskActivityLog::whereDate('created_at', $currentDate)
                ->whereIn('action', ['updated', 'status_changed', 'priority_changed', 'moved'])
                ->count();
            
            $taskChangesOverTime[$dateKey] = [
                'date' => $currentDate->format('M d'),
                'changes' => $changesOnDate,
            ];
            $currentDate->addDay();
        }
        
        // Task changes by user (who made most changes)
        $taskChangesByUser = \App\Models\TaskActivityLog::whereIn('action', ['updated', 'status_changed', 'priority_changed', 'moved'])
            ->where('created_at', '>=', $startDate)
            ->with('user')
            ->get()
            ->groupBy('user_id')
            ->map(function($logs, $userId) {
                $user = User::find($userId);
                if (!$user) return null;
                return [
                    'user' => $user,
                    'changes_count' => $logs->count(),
                ];
            })
            ->filter()
            ->sortByDesc('changes_count')
            ->take(10)
            ->values();
        
        // Get all users for assignment dropdown
        $users = User::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        // Get custom boards
        $customBoards = CustomBoard::orderBy('order')->get();
        
        // Task List Statistics
        // Task Lists by User (who created most task lists)
        $taskListsByUser = TaskList::with('user')
            ->get()
            ->groupBy('user_id')
            ->map(function($lists, $userId) {
                $user = User::find($userId);
                if (!$user) return null;
                return [
                    'user' => $user,
                    'task_lists_count' => $lists->count(),
                    'total_tasks' => $lists->sum(function($list) {
                        return $list->tasks()->count();
                    }),
                ];
            })
            ->filter()
            ->sortByDesc('task_lists_count')
            ->take(10)
            ->values();
        
        // Task Lists Creation Trends (last 30 days)
        $taskListCreationTrends = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $listsOnDate = TaskList::whereDate('created_at', $currentDate)->count();
            
            $taskListCreationTrends[$dateKey] = [
                'date' => $currentDate->format('M d'),
                'created' => $listsOnDate,
            ];
            $currentDate->addDay();
        }
        
        // Tasks per Task List (top task lists by task count)
        $tasksPerTaskList = TaskList::with(['user', 'tasks'])
            ->get()
            ->map(function($list) {
                return [
                    'task_list' => [
                        'id' => $list->id,
                        'name' => $list->name,
                        'user_name' => $list->user->name ?? 'Unknown',
                    ],
                    'total_tasks' => $list->tasks()->count(),
                    'completed_tasks' => $list->tasks()->where('status', 'done')->count(),
                    'in_progress_tasks' => $list->tasks()->where('status', 'in_progress')->count(),
                    'todo_tasks' => $list->tasks()->where('status', 'todo')->count(),
                ];
            })
            ->sortByDesc('total_tasks')
            ->take(10)
            ->values();
        
        // Task Lists with Most Tasks (line graph over time)
        $taskListGrowthOverTime = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $totalLists = TaskList::where('created_at', '<=', $currentDate->endOfDay())->count();
            $totalTasksInLists = Task::whereNotNull('task_list_id')
                ->where('created_at', '<=', $currentDate->endOfDay())
                ->count();
            
            $taskListGrowthOverTime[$dateKey] = [
                'date' => $currentDate->format('M d'),
                'total_lists' => $totalLists,
                'total_tasks' => $totalTasksInLists,
            ];
            $currentDate->addDay();
        }
        
        // Format tasks for JSON (for JavaScript)
        $tasksJson = $tasks->map(function($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'notes' => $task->notes,
                'status' => $task->status,
                'type' => $task->type,
                'parent_id' => $task->parent_id,
                'due_date' => $task->due_date ? $task->due_date->toISOString() : null,
                'priority' => $task->priority,
                'created_by' => $task->created_by,
                'creator' => [
                    'id' => $task->creator->id,
                    'name' => $task->creator->name,
                ],
                'subtasks' => $task->subtasks->map(function($subtask) {
                    return [
                        'id' => $subtask->id,
                        'title' => $subtask->title,
                        'description' => $subtask->description,
                        'status' => $subtask->status,
                        'due_date' => $subtask->due_date ? $subtask->due_date->toISOString() : null,
                    ];
                })->toArray(),
                'attachments' => $task->attachments->map(function($attachment) {
                    return [
                        'id' => $attachment->id,
                        'file_name' => $attachment->file_name,
                        'file_type' => $attachment->file_type,
                        'file_url' => $attachment->file_url,
                        'created_at' => $attachment->created_at ? $attachment->created_at->toISOString() : null,
                    ];
                })->toArray(),
                'comments' => $task->comments->map(function($comment) {
                    return [
                        'id' => $comment->id,
                        'comment' => $comment->comment,
                        'created_at' => $comment->created_at->toISOString(),
                        'user' => [
                            'id' => $comment->user->id,
                            'name' => $comment->user->name,
                        ],
                    ];
                })->toArray(),
                'assignments' => $task->assignments->map(function($assignment) {
                    return [
                        'user_id' => $assignment->user_id,
                        'role' => $assignment->role,
                        'user' => [
                            'id' => $assignment->user->id,
                            'name' => $assignment->user->name,
                        ],
                    ];
                })->toArray(),
            ];
        })->toArray();
        
        // Get custom priorities
        $customPriorities = CustomPriority::forUser($user->id)->get();
        
        return view('admin.tasks.dashboard', compact(
            'tasks', 
            'tasksByStatus', 
            'view', 
            'users', 
            'tasksJson', 
            'customBoards', 
            'customPriorities', 
            'statusFilter', 
            'typeFilter',
            'statusDistribution',
            'typeDistribution',
            'creationTrends',
            'completionRate',
            'priorityDistribution',
            'tasksByUser',
            'usersWithMostCompleted',
            'taskChangesOverTime',
            'taskChangesByUser',
            'taskListsByUser',
            'taskListCreationTrends',
            'tasksPerTaskList',
            'taskListGrowthOverTime'
        ));
    }
    
    /**
     * Get chart data for AJAX requests
     */
    public function getChartData(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isSuperAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $statusFilter = $request->get('status');
        $typeFilter = $request->get('type', 'all');
        
        // Get filtered tasks
        $tasksQuery = Task::whereNull('parent_id')
            ->with([
                'creator',
                'assignments.user',
                'attachments',
                'comments.user',
                'subtasks',
                'taskList'
            ])
            ->orderBy('created_at', 'desc');
        
        if ($statusFilter) {
            $tasksQuery->where('status', $statusFilter);
        }
        
        if ($typeFilter && $typeFilter !== 'all') {
            $tasksQuery->where('type', $typeFilter);
        }
        
        $tasks = $tasksQuery->get();
        
        // Calculate all chart data (same logic as dashboard method)
        $statusDistribution = [
            'todo' => $tasks->where('status', 'todo')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'done' => $tasks->where('status', 'done')->count(),
        ];
        
        $typeDistribution = [
            'personal' => $tasks->where('type', 'personal')->count(),
            'group' => $tasks->where('type', 'group')->count(),
        ];
        
        $startDate = \Carbon\Carbon::now()->subDays(30);
        $endDate = \Carbon\Carbon::now();
        $currentDate = $startDate->copy();
        
        $creationTrends = [];
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $tasksOnDate = $tasks->filter(function($task) use ($currentDate) {
                return $task->created_at && $task->created_at->isSameDay($currentDate);
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
        
        $completionRate = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $tasksUpToDate = Task::whereNull('parent_id')
                ->where('created_at', '<=', $currentDate->endOfDay())
                ->when($typeFilter && $typeFilter !== 'all', function($q) use ($typeFilter) {
                    $q->where('type', $typeFilter);
                })
                ->get();
            
            $total = $tasksUpToDate->count();
            $completed = $tasksUpToDate->where('status', 'done')->count();
            
            $completionRate[$dateKey] = [
                'date' => $currentDate->format('M d'),
                'total' => $total,
                'completed' => $completed,
                'rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            ];
            $currentDate->addDay();
        }
        
        $priorityDistribution = [
            'high' => $tasks->where('priority', 'high')->count(),
            'medium' => $tasks->where('priority', 'medium')->count(),
            'low' => $tasks->where('priority', 'low')->count(),
        ];
        
        $tasksByUser = $tasks->groupBy('created_by')
            ->map(function($userTasks, $userId) {
                $user = User::find($userId);
                if (!$user) return null;
                return [
                    'user' => ['id' => $user->id, 'name' => $user->name],
                    'total' => $userTasks->count(),
                    'completed' => $userTasks->where('status', 'done')->count(),
                ];
            })
            ->filter()
            ->sortByDesc('total')
            ->take(10)
            ->values();
        
        $usersWithMostCompleted = Task::whereNull('parent_id')
            ->where('status', 'done')
            ->when($typeFilter && $typeFilter !== 'all', function($q) use ($typeFilter) {
                $q->where('type', $typeFilter);
            })
            ->with('creator')
            ->get()
            ->groupBy('created_by')
            ->map(function($userTasks, $userId) {
                $user = User::find($userId);
                if (!$user) return null;
                return [
                    'user' => ['id' => $user->id, 'name' => $user->name],
                    'completed_count' => $userTasks->count(),
                ];
            })
            ->filter()
            ->sortByDesc('completed_count')
            ->take(10)
            ->values();
        
        $taskChangesOverTime = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $changesOnDate = TaskActivityLog::whereDate('created_at', $currentDate)
                ->whereIn('action', ['updated', 'status_changed', 'priority_changed', 'moved'])
                ->count();
            
            $taskChangesOverTime[$dateKey] = [
                'date' => $currentDate->format('M d'),
                'changes' => $changesOnDate,
            ];
            $currentDate->addDay();
        }
        
        $taskChangesByUser = TaskActivityLog::whereIn('action', ['updated', 'status_changed', 'priority_changed', 'moved'])
            ->where('created_at', '>=', $startDate)
            ->with('user')
            ->get()
            ->groupBy('user_id')
            ->map(function($logs, $userId) {
                $user = User::find($userId);
                if (!$user) return null;
                return [
                    'user' => ['id' => $user->id, 'name' => $user->name],
                    'changes_count' => $logs->count(),
                ];
            })
            ->filter()
            ->sortByDesc('changes_count')
            ->take(10)
            ->values();
        
        // Task List Statistics (same logic as dashboard method)
        $taskListsByUser = TaskList::with('user')
            ->get()
            ->groupBy('user_id')
            ->map(function($lists, $userId) {
                $user = User::find($userId);
                if (!$user) return null;
                return [
                    'user' => ['id' => $user->id, 'name' => $user->name],
                    'task_lists_count' => $lists->count(),
                    'total_tasks' => $lists->sum(function($list) {
                        return $list->tasks()->count();
                    }),
                ];
            })
            ->filter()
            ->sortByDesc('task_lists_count')
            ->take(10)
            ->values();
        
        $taskListCreationTrends = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $listsOnDate = TaskList::whereDate('created_at', $currentDate)->count();
            
            $taskListCreationTrends[$dateKey] = [
                'date' => $currentDate->format('M d'),
                'created' => $listsOnDate,
            ];
            $currentDate->addDay();
        }
        
        $tasksPerTaskList = TaskList::with(['user', 'tasks'])
            ->get()
            ->map(function($list) {
                return [
                    'task_list' => [
                        'id' => $list->id,
                        'name' => $list->name,
                        'user_name' => $list->user->name ?? 'Unknown',
                    ],
                    'total_tasks' => $list->tasks()->count(),
                    'completed_tasks' => $list->tasks()->where('status', 'done')->count(),
                    'in_progress_tasks' => $list->tasks()->where('status', 'in_progress')->count(),
                    'todo_tasks' => $list->tasks()->where('status', 'todo')->count(),
                ];
            })
            ->sortByDesc('total_tasks')
            ->take(10)
            ->values();
        
        $taskListGrowthOverTime = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $totalLists = TaskList::where('created_at', '<=', $currentDate->endOfDay())->count();
            $totalTasksInLists = Task::whereNotNull('task_list_id')
                ->where('created_at', '<=', $currentDate->endOfDay())
                ->count();
            
            $taskListGrowthOverTime[$dateKey] = [
                'date' => $currentDate->format('M d'),
                'total_lists' => $totalLists,
                'total_tasks' => $totalTasksInLists,
            ];
            $currentDate->addDay();
        }
        
        return response()->json([
            'statusDistribution' => $statusDistribution,
            'typeDistribution' => $typeDistribution,
            'creationTrends' => $creationTrends,
            'completionRate' => $completionRate,
            'priorityDistribution' => $priorityDistribution,
            'tasksByUser' => $tasksByUser,
            'usersWithMostCompleted' => $usersWithMostCompleted,
            'taskChangesOverTime' => $taskChangesOverTime,
            'taskChangesByUser' => $taskChangesByUser,
            'taskListsByUser' => $taskListsByUser,
            'taskListCreationTrends' => $taskListCreationTrends,
            'tasksPerTaskList' => $tasksPerTaskList,
            'taskListGrowthOverTime' => $taskListGrowthOverTime,
        ]);
    }
    
    /**
     * Display a listing of tasks (My Tasks or Group Tasks)
     */
    public function index(Request $request)
    {
        $type = $request->get('type', 'personal'); // 'personal' or 'group'
        $view = $request->get('view', 'board'); // 'board' or 'list'
        $taskListId = $request->get('list_id'); // Selected task list ID
        
        $user = Auth::user();
        
        // Initialize taskLists for all types (empty for group tasks)
        $taskLists = collect();
        
        if ($type === 'personal') {
            // Get all task lists for this user (owned + shared)
            $ownedTaskListIds = TaskList::forUser($user->id)->pluck('id')->toArray();
            $sharedTaskListIds = TaskListInvitation::where('user_id', $user->id)
                ->where('status', 'accepted')
                ->pluck('task_list_id')
                ->toArray();
            
            $allTaskListIds = array_unique(array_merge($ownedTaskListIds, $sharedTaskListIds));
            $taskLists = TaskList::whereIn('id', $allTaskListIds)->orderBy('order')->get();
            
            // If no task list selected and lists exist, select the first one
            if (!$taskListId && $taskLists->isNotEmpty()) {
                $taskListId = $taskLists->first()->id;
            }
            
            // My Tasks - tasks from lists owned by user or shared with user
            $tasksQuery = Task::where('type', 'personal')
                ->whereNull('parent_id'); // Only show parent tasks
            
            // Filter by task list if selected
            if ($taskListId) {
                // Show tasks from this list if user owns it or it's shared with them
                if (in_array($taskListId, $allTaskListIds)) {
                    $tasksQuery->where('task_list_id', $taskListId);
                } else {
                    // User doesn't have access to this list
                    $tasksQuery->whereRaw('1 = 0'); // Return no results
                }
            } else {
                // If no list selected and no lists exist, show tasks without a list that user created
                $tasksQuery->whereNull('task_list_id')
                    ->where('created_by', $user->id);
            }
            
            $tasks = $tasksQuery->with(['creator', 'attachments', 'comments.user', 'subtasks', 'taskList'])
                ->orderBy('order')
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Group Tasks - show ALL tasks where user is:
            // 1. The creator (owner)
            // 2. Has an accepted assignment (joined via code, link, or accepted invitation)
            // 3. Has a pending invitation
            $assignedTaskIds = TaskAssignment::where('user_id', $user->id)
                ->pluck('task_id')
                ->toArray();
            
            $invitedTaskIds = TaskInvitation::where('user_id', $user->id)
                ->where('status', 'pending')
                ->pluck('task_id')
                ->toArray();
            
            // Get all group tasks where user is involved
            // This includes: created by user, assigned to user (joined), or has pending invitation
            $query = Task::where('type', 'group')
                ->where(function($q) use ($user, $assignedTaskIds, $invitedTaskIds) {
                    $q->where('created_by', $user->id); // User is creator
                    
                    // User has accepted assignment (joined via code, link, or accepted invitation)
                    if (!empty($assignedTaskIds)) {
                        $q->orWhereIn('id', $assignedTaskIds);
                    }
                    
                    // User has pending invitation
                    if (!empty($invitedTaskIds)) {
                        $q->orWhereIn('id', $invitedTaskIds);
                    }
                })
                ->with([
                    'creator', 
                    'assignments.user', 
                    'attachments', 
                    'comments.user', 
                    'invitations' => function($q) use ($user) {
                        $q->where('user_id', $user->id)->where('status', 'pending');
                    }
                ])
                ->orderBy('order')
                ->orderBy('created_at', 'desc');
            
            $tasks = $query->get();
            
            // All tasks where user has joined (via code, link, or invitation) are included
            // Multiple group tasks can be joined and will all appear in the list
        }
        
        // Get or create default custom boards for this user, type, and task list
        $this->ensureDefaultBoards($user->id, $type, $taskListId);
        
        // Get custom boards for this user, type, and task list
        $customBoards = CustomBoard::forUser($user->id, $type, $taskListId)->get();
        
        // Group tasks by status dynamically based on custom boards
        // Only include parent tasks (not subtasks) in the board view
        $tasksByStatus = [];
        foreach ($customBoards as $board) {
            $tasksByStatus[$board->status_key] = $tasks->where('status', $board->status_key)
                ->whereNull('parent_id') // Only parent tasks
                ->values();
        }
        
        // Also include any tasks with statuses not in custom boards (fallback)
        // Only parent tasks
        foreach ($tasks->whereNull('parent_id') as $task) {
            if (!isset($tasksByStatus[$task->status])) {
                $tasksByStatus[$task->status] = collect([$task]);
            }
        }
        
        // Get all users for group task assignment
        $users = User::where('is_active', true)
            ->where('id', '!=', $user->id)
            ->orderBy('name')
            ->get();
        
        // Format tasks for JSON (for JavaScript)
        $tasksJson = $tasks->map(function($task) use ($user) {
            // Check if user is owner
            $isOwner = $task->created_by === $user->id;
            
            // Check if user has assignment
            $userAssignment = $task->assignments->firstWhere('user_id', $user->id);
            $isMember = $userAssignment !== null;
            
            // Check if user has pending invitation
            $pendingInvitation = $task->invitations->firstWhere('user_id', $user->id);
            $hasPendingInvitation = $pendingInvitation !== null;
            
            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'notes' => $task->notes,
                'status' => $task->status,
                'type' => $task->type,
                'parent_id' => $task->parent_id,
                'due_date' => $task->due_date ? $task->due_date->toISOString() : null,
                'invite_code' => $task->invite_code,
                'is_owner' => $isOwner,
                'is_member' => $isMember,
                'has_pending_invitation' => $hasPendingInvitation,
                'pending_invitation_id' => $pendingInvitation ? $pendingInvitation->id : null,
                'subtasks' => $task->subtasks->map(function($subtask) {
                    return [
                        'id' => $subtask->id,
                        'title' => $subtask->title,
                        'description' => $subtask->description,
                        'status' => $subtask->status,
                        'due_date' => $subtask->due_date ? $subtask->due_date->toISOString() : null,
                    ];
                })->toArray(),
                'attachments' => $task->attachments->map(function($attachment) {
                    return [
                        'id' => $attachment->id,
                        'file_name' => $attachment->file_name,
                        'file_type' => $attachment->file_type,
                        'file_url' => $attachment->file_url,
                        'created_at' => $attachment->created_at ? $attachment->created_at->toISOString() : null,
                    ];
                })->toArray(),
                'comments' => $task->type === 'group' ? $task->comments->map(function($comment) {
                    return [
                        'id' => $comment->id,
                        'comment' => $comment->comment,
                        'created_at' => $comment->created_at->toISOString(),
                        'user' => [
                            'id' => $comment->user->id,
                            'name' => $comment->user->name,
                        ],
                    ];
                })->toArray() : [],
                'assignments' => $task->assignments->map(function($assignment) {
                    return [
                        'user_id' => $assignment->user_id,
                        'role' => $assignment->role,
                        'user' => [
                            'id' => $assignment->user->id,
                            'name' => $assignment->user->name,
                        ],
                    ];
                })->toArray(),
            ];
        })->toArray();
        
        // Get custom priorities for the user
        $customPriorities = CustomPriority::forUser($user->id)->get();
        
        return view('admin.tasks.index', compact('tasks', 'tasksByStatus', 'type', 'view', 'users', 'tasksJson', 'customBoards', 'taskLists', 'taskListId', 'customPriorities'));
    }

    /**
     * Store a newly created task
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'notes' => 'nullable|string|max:10000',
            'type' => 'required|in:personal,group',
            'priority' => 'nullable|string|max:50',
            'task_list_id' => 'nullable|exists:task_lists,id',
            'due_date' => 'nullable|date',
            'due_time' => 'nullable|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        
        // Get max order for the status
        $maxOrder = Task::where('type', $request->type)
            ->where('status', 'todo')
            ->where('created_by', $user->id)
            ->max('order') ?? 0;

        $dueDate = null;
        if ($request->due_date) {
            $dueDate = $request->due_date;
            if ($request->due_time) {
                $dueDate .= ' ' . $request->due_time . ':00';
            } else {
                $dueDate .= ' 23:59:59';
            }
        }

        // If parent_id is provided, validate it's a personal task and belongs to the user
        $parentId = null;
        if ($request->parent_id) {
            $parentTask = Task::find($request->parent_id);
            if ($parentTask && $parentTask->type === 'personal' && $parentTask->created_by === $user->id) {
                $parentId = $request->parent_id;
            }
        }

        // Validate task_list_id for personal tasks
        $taskListId = null;
        if ($request->type === 'personal' && $request->task_list_id) {
            $taskList = TaskList::find($request->task_list_id);
            if ($taskList && $taskList->user_id === $user->id) {
                $taskListId = $request->task_list_id;
            }
        }

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'notes' => $request->notes,
            'type' => $request->type,
            'priority' => $request->priority ?? 'medium',
            'task_list_id' => $taskListId,
            'status' => 'todo',
            'created_by' => $user->id,
            'parent_id' => $parentId,
            'due_date' => $dueDate ? date('Y-m-d H:i:s', strtotime($dueDate)) : null,
            'order' => $maxOrder + 1,
        ]);

        // Log activity
        $this->logTaskActivity($task, 'created', null, null, 'Task created: ' . $task->title);

        // For group tasks, assign the creator as owner and generate invite code
        if ($request->type === 'group') {
            TaskAssignment::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'role' => 'owner',
            ]);
            
            // Generate invite code for group task
            $task->generateInviteCode();
        }

        $task->load(['creator', 'attachments', 'comments.user', 'assignments.user', 'subtasks', 'parent']);

        // Format task for JSON response
        $taskData = [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'notes' => $task->notes,
            'status' => $task->status,
            'type' => $task->type,
            'parent_id' => $task->parent_id,
            'due_date' => $task->due_date ? $task->due_date->toISOString() : null,
            'created_by' => $task->created_by,
            'subtasks' => $task->subtasks->map(function($subtask) {
                return [
                    'id' => $subtask->id,
                    'title' => $subtask->title,
                    'description' => $subtask->description,
                    'status' => $subtask->status,
                    'due_date' => $subtask->due_date ? $subtask->due_date->toISOString() : null,
                ];
            })->toArray(),
            'attachments' => $task->attachments->map(function($attachment) {
                return [
                    'id' => $attachment->id,
                    'file_name' => $attachment->file_name,
                    'file_type' => $attachment->file_type,
                    'file_url' => $attachment->file_url,
                ];
            })->toArray(),
            'comments' => $task->comments->map(function($comment) {
                return [
                    'id' => $comment->id,
                    'comment' => $comment->comment,
                    'created_at' => $comment->created_at->toISOString(),
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                    ],
                ];
            })->toArray(),
            'assignments' => $task->assignments->map(function($assignment) {
                return [
                    'user_id' => $assignment->user_id,
                    'role' => $assignment->role,
                    'user' => [
                        'id' => $assignment->user->id,
                        'name' => $assignment->user->name,
                    ],
                ];
            })->toArray(),
        ];

        return response()->json([
            'success' => true,
            'task' => $taskData,
            'message' => 'Task created successfully.'
        ]);
    }

    /**
     * Update a task
     */
    public function update(Request $request, Task $task)
    {
        // Check permission
        if ($task->type === 'personal' && $task->created_by !== Auth::id()) {
            abort(403, 'You do not have permission to update this task.');
        }
        
        if ($task->type === 'group') {
            $assignment = TaskAssignment::where('task_id', $task->id)
                ->where('user_id', Auth::id())
                ->first();
            if (!$assignment || !in_array($assignment->role, ['owner', 'assignee'])) {
                abort(403, 'You do not have permission to update this task.');
            }
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'notes' => 'nullable|string|max:10000',
            'status' => 'sometimes|string|max:50', // Allow custom statuses
            'priority' => 'sometimes|string|max:50',
            'due_date' => 'nullable|date',
            'due_time' => 'nullable|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = [];
        
        if ($request->has('title')) {
            $updateData['title'] = $request->title;
        }
        
        if ($request->has('description')) {
            $updateData['description'] = $request->description;
        }
        
        if ($request->has('notes')) {
            $updateData['notes'] = $request->notes;
        }
        
        // Track changes for logging
        $changes = [];
        
        if ($request->has('status')) {
            $updateData['status'] = $request->status;
            
            // Log status change
            if ($request->status !== $task->status) {
                $this->logTaskActivity($task, 'status_changed', 'status', $task->status, $request->status, "Status changed from {$task->status} to {$request->status}");
            }
            
            // Update order when status changes (if order not explicitly provided)
            if ($request->status !== $task->status && !$request->has('order')) {
                $maxOrder = Task::where('type', $task->type)
                    ->where('status', $request->status)
                    ->where('created_by', $task->created_by)
                    ->max('order') ?? 0;
                $updateData['order'] = $maxOrder + 1;
            }
        }
        
        // Handle order update (for reordering within same board or when moving)
        if ($request->has('order')) {
            $newOrder = (int) $request->order;
            $oldOrder = $task->order ?? 0;
            
            // Get all tasks in the same status/type/created_by
            $query = Task::where('type', $task->type)
                ->where('status', $request->has('status') ? $request->status : $task->status)
                ->where('created_by', $task->created_by)
                ->where('id', '!=', $task->id);
            
            // If it's a personal task with task_list_id, also filter by that
            if ($task->type === 'personal' && $task->task_list_id) {
                $query->where('task_list_id', $task->task_list_id);
            }
            
            $tasksInSameBoard = $query->orderBy('order')->get();
            
            // Reorder tasks
            $order = 0;
            foreach ($tasksInSameBoard as $t) {
                if ($order === $newOrder) {
                    $order++; // Skip the position for the moved task
                }
                if ($t->order != $order) {
                    $t->update(['order' => $order]);
                }
                $order++;
            }
            
            $updateData['order'] = $newOrder;
        }
        
        if ($request->has('priority')) {
            $updateData['priority'] = $request->priority;
            
            // Log priority change
            if ($request->priority !== $task->priority) {
                $this->logTaskActivity($task, 'priority_changed', 'priority', $task->priority, $request->priority, "Priority changed from {$task->priority} to {$request->priority}");
            }
        }
        
        if ($request->has('due_date')) {
            $dueDate = $request->due_date;
            if ($request->due_time) {
                $dueDate .= ' ' . $request->due_time . ':00';
            } elseif ($request->due_date && !$request->has('due_time')) {
                $dueDate .= ' 23:59:59';
            }
            $newDueDate = $dueDate ? date('Y-m-d H:i:s', strtotime($dueDate)) : null;
            
            // Log due date change
            if ($task->due_date != $newDueDate) {
                $oldDueDate = $task->due_date ? $task->due_date->format('Y-m-d H:i:s') : null;
                $this->logTaskActivity($task, 'due_date_changed', 'due_date', $oldDueDate, $newDueDate, "Due date changed from " . ($oldDueDate ?? 'none') . " to " . ($newDueDate ?? 'none'));
            }
            $updateData['due_date'] = $newDueDate;
        }
        
        // Log task_list_id change if applicable
        if ($request->has('task_list_id') && $task->task_list_id != $request->task_list_id) {
            $oldListId = $task->task_list_id;
            $newListId = $request->task_list_id;
            $oldListName = $oldListId ? TaskList::find($oldListId)->name ?? 'Unknown' : 'None';
            $newListName = $newListId ? TaskList::find($newListId)->name ?? 'Unknown' : 'None';
            $this->logTaskActivity($task, 'task_list_changed', 'task_list_id', $oldListId, $newListId, "Task moved from list '{$oldListName}' to '{$newListName}'");
            $updateData['task_list_id'] = $newListId;
        }

        $task->update($updateData);
        
        // Log general update if other fields changed
        if ($request->has('title') && $request->title !== $task->getOriginal('title')) {
            $this->logTaskActivity($task, 'title_changed', 'title', $task->getOriginal('title'), $request->title, "Title changed from '{$task->getOriginal('title')}' to '{$request->title}'");
        }
        
        if ($request->has('description') && $request->description !== $task->getOriginal('description')) {
            $this->logTaskActivity($task, 'description_changed', 'description', $task->getOriginal('description'), $request->description, 'Description updated');
        }
        
        if ($request->has('notes') && $request->notes !== $task->getOriginal('notes')) {
            $this->logTaskActivity($task, 'notes_changed', 'notes', $task->getOriginal('notes'), $request->notes, 'Notes updated');
        }
        
        // Log order change if it happened without status change
        if ($request->has('order') && !$request->has('status')) {
            $oldOrder = $task->getOriginal('order') ?? 0;
            $newOrder = (int) $request->order;
            if ($oldOrder != $newOrder) {
                $this->logTaskActivity($task, 'reordered', 'order', $oldOrder, $newOrder, "Task reordered from position {$oldOrder} to {$newOrder} within the same board");
            }
        }
        $task->load(['creator', 'attachments', 'comments.user', 'assignments.user']);

        // Format task for JSON response
        $taskData = $task->toArray();
        $taskData['attachments'] = $task->attachments->map(function($attachment) {
            return [
                'id' => $attachment->id,
                'file_name' => $attachment->file_name,
                'file_type' => $attachment->file_type,
                'file_url' => $attachment->file_url,
            ];
        })->toArray();

        return response()->json([
            'success' => true,
            'task' => $taskData,
            'message' => 'Task updated successfully.'
        ]);
    }

    /**
     * Update task order (for drag and drop)
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:tasks,id',
            'tasks.*.status' => 'required|in:todo,in_progress,done',
            'tasks.*.order' => 'required|integer',
        ]);

        foreach ($request->tasks as $taskData) {
            $task = Task::find($taskData['id']);
            if ($task) {
                $oldStatus = $task->status;
                $oldOrder = $task->order;
                
                $task->update([
                    'status' => $taskData['status'],
                    'order' => $taskData['order'],
                ]);
                
                // Log move if status changed
                if ($oldStatus !== $taskData['status']) {
                    $this->logTaskActivity($task, 'moved', 'status', $oldStatus, $taskData['status'], "Task moved from {$oldStatus} to {$taskData['status']}", [
                        'old_order' => $oldOrder,
                        'new_order' => $taskData['order']
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Task order updated successfully.'
        ]);
    }

    /**
     * Reorder a task within the same board
     */
    public function reorder(Request $request, Task $task)
    {
        $request->validate([
            'status' => 'required|string',
            'order' => 'required|integer|min:0',
        ]);

        $user = Auth::user();
        
        // Check permission
        if ($task->type === 'personal' && $task->created_by !== $user->id) {
            abort(403, 'You do not have permission to reorder this task.');
        }

        $newOrder = (int) $request->order;
        $oldOrder = $task->order ?? 0;
        
        // Get all tasks in the same status/type/created_by
        $query = Task::where('type', $task->type)
            ->where('status', $request->status)
            ->where('created_by', $task->created_by)
            ->where('id', '!=', $task->id);
        
        // If it's a personal task with task_list_id, also filter by that
        if ($task->type === 'personal' && $task->task_list_id) {
            $query->where('task_list_id', $task->task_list_id);
        }
        
        $tasksInSameBoard = $query->orderBy('order')->get();
        
        // Reorder tasks: shift other tasks to make room
        $order = 0;
        foreach ($tasksInSameBoard as $t) {
            if ($order === $newOrder) {
                $order++; // Skip the position for the moved task
            }
            if ($t->order != $order) {
                $t->update(['order' => $order]);
            }
            $order++;
        }
        
        // Check if status changed
        $oldStatus = $task->status;
        $statusChanged = $oldStatus !== $request->status;
        
        // Update the moved task
        $task->update([
            'order' => $newOrder,
            'status' => $request->status // Ensure status matches
        ]);
        
        // Log the reorder or move
        if ($statusChanged) {
            $this->logTaskActivity($task, 'moved', 'status', $oldStatus, $request->status, "Task moved from {$oldStatus} to {$request->status} and reordered to position {$newOrder}", [
                'old_order' => $oldOrder,
                'new_order' => $newOrder
            ]);
        } else {
            $this->logTaskActivity($task, 'reordered', 'order', $oldOrder, $newOrder, "Task reordered from position {$oldOrder} to {$newOrder}");
        }

        return response()->json([
            'success' => true,
            'message' => 'Task reordered successfully.'
        ]);
    }

    /**
     * Delete a task
     */
    public function destroy(Request $request, Task $task)
    {
        // Check permission
        $user = Auth::user();
        if ($task->type === 'personal' && $task->created_by !== $user->id) {
            abort(403, 'You do not have permission to delete this task.');
        }
        
        if ($task->type === 'group') {
            $assignment = TaskAssignment::where('task_id', $task->id)
                ->where('user_id', $user->id)
                ->first();
            if (!$assignment || $assignment->role !== 'owner') {
                abort(403, 'Only task owners can delete tasks.');
            }
        }

        // Delete attachments from storage
        foreach ($task->attachments as $attachment) {
            try {
                // Check if DigitalOcean Spaces is configured
                $useDigitalOcean = !empty(env('DIGITALOCEAN_SPACES_KEY')) 
                    && !empty(env('DIGITALOCEAN_SPACES_SECRET')) 
                    && !empty(env('DIGITALOCEAN_SPACES_BUCKET'));
                
                if ($useDigitalOcean) {
                    if (Storage::disk('digitalocean')->exists($attachment->file_path)) {
                        Storage::disk('digitalocean')->delete($attachment->file_path);
                    }
                } else {
                    // Fallback to public disk
                    if (Storage::disk('public')->exists($attachment->file_path)) {
                        Storage::disk('public')->delete($attachment->file_path);
                    }
                }
            } catch (\Exception $e) {
                // Try fallback to public disk
                try {
                    if (Storage::disk('public')->exists($attachment->file_path)) {
                        Storage::disk('public')->delete($attachment->file_path);
                    }
                } catch (\Exception $e2) {
                    // Ignore deletion errors
                }
            }
        }

        // Log deletion before deleting
        $this->logTaskActivity($task, 'deleted', null, null, null, "Task deleted: {$task->title}", [
            'title' => $task->title,
            'status' => $task->status,
            'priority' => $task->priority,
        ]);

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully.'
        ]);
    }

    /**
     * Add comment to task
     */
    public function addComment(Request $request, Task $task)
    {
        $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        // Personal tasks cannot have comments
        if ($task->type === 'personal') {
            abort(400, 'Personal tasks do not support comments. Use notes instead.');
        }
        
        // Check permission for group tasks
        if ($task->type === 'group') {
            $assignment = TaskAssignment::where('task_id', $task->id)
                ->where('user_id', Auth::id())
                ->first();
            if (!$assignment) {
                abort(403, 'You do not have permission to comment on this task.');
            }
        }

        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'comment' => $request->comment,
        ]);

        // Log comment addition
        $this->logTaskActivity($task, 'comment_added', 'comment', null, $request->comment, "Comment added to task", [
            'comment_id' => $comment->id,
            'comment_length' => strlen($request->comment)
        ]);

        $comment->load('user');

        return response()->json([
            'success' => true,
            'comment' => [
                'id' => $comment->id,
                'comment' => $comment->comment,
                'created_at' => $comment->created_at->toISOString(),
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'email' => $comment->user->email,
                ],
            ],
            'message' => 'Comment added successfully.'
        ]);
    }

    /**
     * Upload attachment to task
     */
    public function uploadAttachment(Request $request, Task $task)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,jpg,png,gif,webp,pdf,doc,docx|max:10240', // 10MB max
        ]);

        // Check permission
        if ($task->type === 'personal' && $task->created_by !== Auth::id()) {
            abort(403, 'You do not have permission to upload files to this task.');
        }
        
        if ($task->type === 'group') {
            $assignment = TaskAssignment::where('task_id', $task->id)
                ->where('user_id', Auth::id())
                ->first();
            if (!$assignment) {
                abort(403, 'You do not have permission to upload files to this task.');
            }
        }

        $file = $request->file('file');
        
        // Use DigitalOcean Spaces if configured, otherwise fallback to public disk
        $assetDisk = 'digitalocean';
        $assetRoot = trim(env('DIGITALOCEAN_SPACES_ROOT_PATH', ''), '/');
        $taskDir = $assetRoot ? $assetRoot . '/tasks/' . $task->id : 'tasks/' . $task->id;
        
        // Check if DigitalOcean Spaces is configured
        $useDigitalOcean = !empty(env('DIGITALOCEAN_SPACES_KEY')) 
            && !empty(env('DIGITALOCEAN_SPACES_SECRET')) 
            && !empty(env('DIGITALOCEAN_SPACES_BUCKET'))
            && !empty(env('DIGITALOCEAN_SPACES_ENDPOINT'));
        
        if (!$useDigitalOcean) {
            // Fallback to public disk if DigitalOcean is not configured
            $assetDisk = 'public';
            $taskDir = 'tasks/' . $task->id;
        }
        
        try {
            $filePath = $file->store($taskDir, $assetDisk);
        } catch (\Exception $e) {
            // If DigitalOcean fails, try public disk as fallback
            if ($assetDisk === 'digitalocean') {
                $assetDisk = 'public';
                $taskDir = 'tasks/' . $task->id;
                $filePath = $file->store($taskDir, $assetDisk);
            } else {
                throw $e;
            }
        }

        $attachment = TaskAttachment::create([
            'task_id' => $task->id,
            'uploaded_by' => Auth::id(),
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);

        // Log attachment upload
        $this->logTaskActivity($task, 'attachment_uploaded', 'attachment', null, $file->getClientOriginalName(), "Attachment uploaded: {$file->getClientOriginalName()}", [
            'attachment_id' => $attachment->id,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);

        $attachment->load('user');

        return response()->json([
            'success' => true,
            'attachment' => [
                'id' => $attachment->id,
                'file_name' => $attachment->file_name,
                'file_type' => $attachment->file_type,
                'file_url' => $attachment->file_url,
                'created_at' => $attachment->created_at ? $attachment->created_at->toISOString() : null,
                'user' => [
                    'id' => $attachment->user->id,
                    'name' => $attachment->user->name,
                ],
            ],
            'message' => 'File uploaded successfully.'
        ]);
    }

    /**
     * Delete attachment
     */
    public function deleteAttachment(TaskAttachment $attachment)
    {
        // Check permission
        $task = $attachment->task;
        if ($task->type === 'personal' && $task->created_by !== Auth::id()) {
            abort(403, 'You do not have permission to delete this attachment.');
        }
        
        if ($task->type === 'group') {
            $assignment = TaskAssignment::where('task_id', $task->id)
                ->where('user_id', Auth::id())
                ->first();
            if (!$assignment || !in_array($assignment->role, ['owner', 'assignee'])) {
                abort(403, 'You do not have permission to delete this attachment.');
            }
        }

        try {
            // Check if DigitalOcean Spaces is configured
            $useDigitalOcean = !empty(env('DIGITALOCEAN_SPACES_KEY')) 
                && !empty(env('DIGITALOCEAN_SPACES_SECRET')) 
                && !empty(env('DIGITALOCEAN_SPACES_BUCKET'));
            
            if ($useDigitalOcean) {
                if (Storage::disk('digitalocean')->exists($attachment->file_path)) {
                    Storage::disk('digitalocean')->delete($attachment->file_path);
                }
            } else {
                // Fallback to public disk
                if (Storage::disk('public')->exists($attachment->file_path)) {
                    Storage::disk('public')->delete($attachment->file_path);
                }
            }
        } catch (\Exception $e) {
            // Try fallback to public disk
            try {
                if (Storage::disk('public')->exists($attachment->file_path)) {
                    Storage::disk('public')->delete($attachment->file_path);
                }
            } catch (\Exception $e2) {
                // Ignore deletion errors
            }
        }

        // Log attachment deletion before deleting
        $this->logTaskActivity($task, 'attachment_deleted', 'attachment', $attachment->file_name, null, "Attachment deleted: {$attachment->file_name}", [
            'attachment_id' => $attachment->id,
            'file_type' => $attachment->file_type,
            'file_size' => $attachment->file_size,
        ]);

        $attachment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attachment deleted successfully.'
        ]);
    }

    /**
     * Assign users to group task
     */
    public function assignUsers(Request $request, Task $task)
    {
        if ($task->type !== 'group') {
            abort(400, 'Only group tasks can have assigned users.');
        }

        // Check permission - only owner can assign
        $assignment = TaskAssignment::where('task_id', $task->id)
            ->where('user_id', Auth::id())
            ->first();
        if (!$assignment || $assignment->role !== 'owner') {
            abort(403, 'Only task owners can assign users.');
        }

        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'roles' => 'required|array',
            'roles.*' => 'in:assignee,viewer',
        ]);

        // Remove existing assignments (except owner)
        TaskAssignment::where('task_id', $task->id)
            ->where('role', '!=', 'owner')
            ->delete();

        // Add new assignments
        foreach ($request->user_ids as $index => $userId) {
            TaskAssignment::create([
                'task_id' => $task->id,
                'user_id' => $userId,
                'role' => $request->roles[$index] ?? 'viewer',
            ]);
        }

        $task->load(['assignments.user']);

        return response()->json([
            'success' => true,
            'task' => $task,
            'message' => 'Users assigned successfully.'
        ]);
    }

    /**
     * Ensure default boards exist for user
     */
    private function ensureDefaultBoards($userId, $type, $taskListId = null)
    {
        // Validate task_list_id exists if provided
        $validTaskListId = null;
        if ($taskListId) {
            $taskList = TaskList::find($taskListId);
            // Allow default boards to be created for any existing task list the user can see
            // (owner or shared); ownership checks are handled elsewhere.
            if ($taskList) {
                $validTaskListId = $taskListId;
            }
            // If task list doesn't exist, leave as null
        }

        $defaultBoards = [
            ['name' => 'To Do', 'status_key' => 'todo', 'color' => 'pink', 'order' => 1],
            ['name' => 'In Progress', 'status_key' => 'in_progress', 'color' => 'orange', 'order' => 2],
            ['name' => 'Done', 'status_key' => 'done', 'color' => 'purple', 'order' => 3],
        ];

        foreach ($defaultBoards as $board) {
            CustomBoard::firstOrCreate(
                [
                    'user_id' => $userId,
                    'type' => $type,
                    'task_list_id' => $validTaskListId,
                    'status_key' => $board['status_key'],
                ],
                [
                    'name' => $board['name'],
                    'color' => $board['color'],
                    'order' => $board['order'],
                    'is_default' => true,
                ]
            );
        }
    }

    /**
     * Store a custom board
     */
    public function storeCustomBoard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:personal,group',
            'color' => 'required|string|max:50',
            'task_list_id' => 'nullable|exists:task_lists,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        
        // Validate task_list_id for personal tasks
        $taskListId = null;
        if ($request->type === 'personal' && $request->task_list_id) {
            $taskList = TaskList::find($request->task_list_id);
            if ($taskList && $taskList->user_id === $user->id) {
                $taskListId = $request->task_list_id;
            }
        }
        
        // Generate unique status_key
        $statusKey = 'custom_' . strtolower(str_replace(' ', '_', preg_replace('/[^a-zA-Z0-9 ]/', '', $request->name)));
        $statusKey = substr($statusKey, 0, 50); // Ensure it fits in the database
        
        // Check if status_key already exists for this user, type, and task list
        $existing = CustomBoard::where('user_id', $user->id)
            ->where('type', $request->type)
            ->where('task_list_id', $taskListId)
            ->where('status_key', $statusKey)
            ->first();
        
        if ($existing) {
            $statusKey = $statusKey . '_' . time();
        }

        // Get max order for this user, type, and task list
        $maxOrder = CustomBoard::where('user_id', $user->id)
            ->where('type', $request->type)
            ->where('task_list_id', $taskListId)
            ->max('order') ?? 0;

        $board = CustomBoard::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'task_list_id' => $taskListId,
            'name' => $request->name,
            'status_key' => $statusKey,
            'color' => $request->color,
            'order' => $maxOrder + 1,
            'is_default' => false,
        ]);

        return response()->json([
            'success' => true,
            'board' => $board,
            'message' => 'Custom board created successfully.'
        ]);
    }

    /**
     * Update a custom board
     */
    public function updateCustomBoard(Request $request, CustomBoard $customBoard)
    {
        // Check permission
        if ($customBoard->user_id !== Auth::id()) {
            abort(403, 'You do not have permission to update this board.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'color' => 'sometimes|required|string|max:50',
            'order' => 'sometimes|integer',
        ]);
        
        // Allow editing name and order for default boards, but not color
        $updateData = [];
        if ($request->has('name')) {
            $updateData['name'] = $request->name;
        }
        if ($request->has('order')) {
            $updateData['order'] = $request->order;
        }
        // Only allow color change for non-default boards
        if (!$customBoard->is_default && $request->has('color')) {
            $updateData['color'] = $request->color;
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $customBoard->update($updateData);

        return response()->json([
            'success' => true,
            'board' => $customBoard->fresh(),
            'message' => 'Board updated successfully.'
        ]);
    }

    /**
     * Delete a custom board
     */
    public function destroyCustomBoard(CustomBoard $customBoard)
    {
        // Check permission
        if ($customBoard->user_id !== Auth::id()) {
            abort(403, 'You do not have permission to delete this board.');
        }

        // Prevent deleting default boards
        if ($customBoard->is_default) {
            abort(400, 'Default boards cannot be deleted.');
        }

        // Move tasks from this board to 'todo' status
        Task::where('status', $customBoard->status_key)
            ->where('created_by', $customBoard->user_id)
            ->where('type', $customBoard->type)
            ->update(['status' => 'todo']);

        $customBoard->delete();

        return response()->json([
            'success' => true,
            'message' => 'Custom board deleted successfully. Tasks moved to "To Do".'
        ]);
    }

    /**
     * Update custom board order
     */
    public function updateCustomBoardOrder(Request $request)
    {
        $request->validate([
            'boards' => 'required|array',
            'boards.*.id' => 'required|exists:custom_boards,id',
            'boards.*.order' => 'required|integer',
        ]);

        $user = Auth::user();

        foreach ($request->boards as $boardData) {
            $board = CustomBoard::find($boardData['id']);
            
            // Check permission
            if ($board && $board->user_id === $user->id) {
                $board->update(['order' => $boardData['order']]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Board order updated successfully.'
        ]);
    }

    /**
     * Toggle lock status of a custom board
     */
    public function toggleCustomBoardLock(CustomBoard $customBoard)
    {
        // Check permission
        if ($customBoard->user_id !== Auth::id()) {
            abort(403, 'You do not have permission to lock/unlock this board.');
        }

        $customBoard->update([
            'is_locked' => !$customBoard->is_locked
        ]);

        return response()->json([
            'success' => true,
            'board' => $customBoard->fresh(),
            'message' => $customBoard->is_locked ? 'Board locked successfully.' : 'Board unlocked successfully.'
        ]);
    }

    /**
     * Store a new task list
     */
    public function storeTaskList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        
        // Get max order
        $maxOrder = TaskList::where('user_id', $user->id)->max('order') ?? 0;

        $taskList = TaskList::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color ?? 'blue',
            'order' => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'taskList' => $taskList,
            'message' => 'Task list created successfully.'
        ]);
    }

    /**
     * Update a task list
     */
    public function updateTaskList(Request $request, TaskList $taskList)
    {
        // Check permission
        if ($taskList->user_id !== Auth::id()) {
            abort(403, 'You do not have permission to update this task list.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $taskList->update($request->only(['name', 'description', 'color']));

        return response()->json([
            'success' => true,
            'taskList' => $taskList,
            'message' => 'Task list updated successfully.'
        ]);
    }

    /**
     * Delete a task list
     */
    public function destroyTaskList(TaskList $taskList)
    {
        // Check permission
        if ($taskList->user_id !== Auth::id()) {
            abort(403, 'You do not have permission to delete this task list.');
        }

        // Tasks will be deleted via cascade, but we can also set their task_list_id to null
        Task::where('task_list_id', $taskList->id)->update(['task_list_id' => null]);
        
        $taskList->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task list deleted successfully.'
        ]);
    }

    /**
     * Generate invite code for task list
     */
    public function generateTaskListInviteCode(TaskList $taskList)
    {
        if ($taskList->user_id !== Auth::id()) {
            abort(403, 'Only task list owners can generate invite codes.');
        }

        $code = $taskList->generateInviteCode();

        return response()->json([
            'success' => true,
            'invite_code' => $code,
            'message' => 'Invite code generated successfully.'
        ]);
    }

    /**
     * Generate share link for task list
     */
    public function generateTaskListShareLink(TaskList $taskList)
    {
        if ($taskList->user_id !== Auth::id()) {
            abort(403, 'Only task list owners can generate share links.');
        }

        // Create or get existing invitation token
        $invitation = TaskListInvitation::where('task_list_id', $taskList->id)
            ->where('status', 'pending')
            ->whereNull('user_id') // Public invitation
            ->first();

        if (!$invitation) {
            $invitation = TaskListInvitation::create([
                'task_list_id' => $taskList->id,
                'user_id' => null, // Public invitation
                'invited_by' => Auth::id(),
                'token' => TaskListInvitation::generateToken(),
                'status' => 'pending',
            ]);
        }

        $shareLink = url('/admin/tasks/task-lists/join-by-link/' . $invitation->token);

        return response()->json([
            'success' => true,
            'share_link' => $shareLink,
            'token' => $invitation->token,
            'message' => 'Share link generated successfully.'
        ]);
    }

    /**
     * Send task list invitation email
     */
    public function sendTaskListInvitationEmail(Request $request, TaskList $taskList)
    {
        if ($taskList->user_id !== Auth::id()) {
            abort(403, 'Only task list owners can send invitation emails.');
        }

        $request->validate([
            'email' => 'required|email',
            'invite_code' => 'required|string',
            'share_link' => 'nullable|string',
        ]);

        try {
            $inviterName = Auth::user()->name;
            
            Mail::to($request->email)->send(
                new TaskListInvitationMail(
                    $taskList->name,
                    $request->invite_code,
                    $request->share_link ?? '',
                    $inviterName
                )
            );

            return response()->json([
                'success' => true,
                'message' => 'Invitation email sent successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send invitation email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Join task list by invite code
     */
    public function joinTaskListByCode(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please log in to join this task list.',
                'requires_login' => true
            ], 401);
        }

        $request->validate([
            'invite_code' => 'required|string|size:8',
        ]);

        $taskList = TaskList::where('invite_code', strtoupper($request->invite_code))
            ->first();

        if (!$taskList) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid invite code.'
            ], 404);
        }

        $user = Auth::user();

        // Check if user is already a member (owner or has accepted invitation)
        if ($taskList->isOwner($user->id) || $taskList->isSharedWith($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are already a member of this task list.'
            ], 400);
        }

        // Check if there's a pending invitation for this user
        $invitation = TaskListInvitation::where('task_list_id', $taskList->id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($invitation) {
            // Accept the existing invitation
            $invitation->update(['status' => 'accepted']);
        } else {
            // Create new invitation and auto-accept
            $invitation = TaskListInvitation::create([
                'task_list_id' => $taskList->id,
                'user_id' => $user->id,
                'invited_by' => $taskList->user_id,
                'token' => TaskListInvitation::generateToken(),
                'status' => 'accepted',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully joined the task list.',
            'task_list' => $taskList->load(['user', 'invitations'])
        ]);
    }

    /**
     * Join task list by invite link (token)
     */
    public function joinTaskListByLink(Request $request, $token)
    {
        if (!Auth::check()) {
            return redirect('/login')
                ->with('error', 'Please log in to join this task list.')
                ->with('redirect', url('/admin/tasks/task-lists/join-by-link/' . $token));
        }

        $invitation = TaskListInvitation::where('token', $token)
            ->where('status', 'pending')
            ->first();

        if (!$invitation || !$invitation->isValid()) {
            return redirect('/admin/tasks?type=personal')
                ->with('error', 'Invalid or expired invitation link.');
        }

        $user = Auth::user();
        $taskList = $invitation->taskList;

        // If invitation is for a specific user, check if it matches
        if ($invitation->user_id && $invitation->user_id !== $user->id) {
            return redirect('/admin/tasks?type=personal')
                ->with('error', 'This invitation is for another user.');
        }

        // Check if user is already a member
        if ($taskList->isOwner($user->id) || $taskList->isSharedWith($user->id)) {
            return redirect('/admin/tasks?type=personal&list_id=' . $taskList->id)
                ->with('info', 'You are already a member of this task list.');
        }

        // Accept invitation
        $invitation->update([
            'status' => 'accepted',
            'user_id' => $user->id, // Set user_id if it was a public invitation
        ]);

        return redirect('/admin/tasks?type=personal&list_id=' . $taskList->id)
            ->with('success', 'Successfully joined the task list: ' . $taskList->name);
    }

    /**
     * Store a custom priority
     */
    public function storeCustomPriority(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'color' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        
        // Check if priority name already exists for this user
        $existing = CustomPriority::where('user_id', $user->id)
            ->where('name', $request->name)
            ->first();
        
        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'A priority with this name already exists.'
            ], 422);
        }
        
        // Get max order
        $maxOrder = CustomPriority::where('user_id', $user->id)->max('order') ?? 0;

        $customPriority = CustomPriority::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'color' => $request->color,
            'order' => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'customPriority' => $customPriority,
            'message' => 'Custom priority created successfully.'
        ]);
    }

    /**
     * Update a custom priority
     */
    public function updateCustomPriority(Request $request, CustomPriority $customPriority)
    {
        // Check permission
        if ($customPriority->user_id !== Auth::id()) {
            abort(403, 'You do not have permission to update this priority.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:50',
            'color' => 'sometimes|required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if another priority with the same name exists (excluding current one)
        if ($request->has('name') && $request->name !== $customPriority->name) {
            $existing = CustomPriority::where('user_id', Auth::id())
                ->where('name', $request->name)
                ->where('id', '!=', $customPriority->id)
                ->first();
            
            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'A priority with this name already exists.'
                ], 422);
            }
        }

        $customPriority->update($request->only(['name', 'color']));

        return response()->json([
            'success' => true,
            'customPriority' => $customPriority,
            'message' => 'Custom priority updated successfully.'
        ]);
    }

    /**
     * Delete a custom priority
     */
    public function destroyCustomPriority(CustomPriority $customPriority)
    {
        // Check permission
        if ($customPriority->user_id !== Auth::id()) {
            abort(403, 'You do not have permission to delete this priority.');
        }

        // Update tasks using this custom priority to 'medium'
        Task::where('created_by', Auth::id())
            ->where('priority', $customPriority->name)
            ->update(['priority' => 'medium']);
        
        $customPriority->delete();

        return response()->json([
            'success' => true,
            'message' => 'Custom priority deleted successfully. Tasks using this priority have been set to "medium".'
        ]);
    }

    /**
     * Generate or regenerate invite code for group task or personal task
     */
    public function generateInviteCode(Task $task)
    {
        // Check permission - only creator can generate invite code
        if ($task->type === 'personal' && $task->created_by !== Auth::id()) {
            abort(403, 'Only task creators can generate invite codes.');
        }
        
        if ($task->type === 'group') {
            $assignment = TaskAssignment::where('task_id', $task->id)
                ->where('user_id', Auth::id())
                ->first();
            if (!$assignment || $assignment->role !== 'owner') {
                abort(403, 'Only task owners can generate invite codes.');
            }
        }

        // For personal tasks, generate invite code (will be used when task is converted)
        // For group tasks, generate or regenerate invite code
        $code = $task->generateInviteCode();

        return response()->json([
            'success' => true,
            'invite_code' => $code,
            'invite_link' => url('/admin/tasks/join-by-link/' . ($task->invitations()->where('status', 'pending')->first()?->token ?? '')),
            'message' => 'Invite code generated successfully.'
        ]);
    }

    /**
     * Get invite link for group task or personal task
     */
    public function getInviteLink(Task $task)
    {
        // Check permission - only creator can get invite link
        if ($task->type === 'personal' && $task->created_by !== Auth::id()) {
            abort(403, 'Only task creators can get invite links.');
        }
        
        if ($task->type === 'group') {
            $assignment = TaskAssignment::where('task_id', $task->id)
                ->where('user_id', Auth::id())
                ->first();
            if (!$assignment || $assignment->role !== 'owner') {
                abort(403, 'Only task owners can get invite links.');
            }
        }

        // Create or get existing invitation token
        $invitation = TaskInvitation::where('task_id', $task->id)
            ->where('status', 'pending')
            ->whereNull('user_id') // Public invitation
            ->first();

        if (!$invitation) {
            $invitation = TaskInvitation::create([
                'task_id' => $task->id,
                'user_id' => null, // Public invitation
                'invited_by' => Auth::id(),
                'token' => TaskInvitation::generateToken(),
                'role' => 'viewer',
                'status' => 'pending',
            ]);
        }

        $inviteLink = url('/admin/tasks/join-by-link/' . $invitation->token);

        return response()->json([
            'success' => true,
            'invite_link' => $inviteLink,
            'token' => $invitation->token,
            'message' => 'Invite link generated successfully.'
        ]);
    }
    
    /**
     * Generate share link for personal task (without converting)
     */
    public function generateShareLink(Task $task)
    {
        // Check permission - only creator can generate share link
        if ($task->type !== 'personal' || $task->created_by !== Auth::id()) {
            abort(403, 'Only personal task creators can generate share links.');
        }

        // Create or get existing invitation token
        $invitation = TaskInvitation::where('task_id', $task->id)
            ->where('status', 'pending')
            ->whereNull('user_id') // Public invitation
            ->first();

        if (!$invitation) {
            $invitation = TaskInvitation::create([
                'task_id' => $task->id,
                'user_id' => null, // Public invitation
                'invited_by' => Auth::id(),
                'token' => TaskInvitation::generateToken(),
                'role' => 'viewer',
                'status' => 'pending',
            ]);
        }

        $shareLink = url('/admin/tasks/join-by-link/' . $invitation->token);

        return response()->json([
            'success' => true,
            'share_link' => $shareLink,
            'token' => $invitation->token,
            'message' => 'Share link generated successfully. Task will be converted to group when someone joins.'
        ]);
    }

    /**
     * Join group task by invite code
     * Also handles personal tasks - converts them to group when someone joins
     * Requires authentication
     */
    public function joinByCode(Request $request)
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please log in to join this task.',
                'requires_login' => true
            ], 401);
        }

        $request->validate([
            'invite_code' => 'required|string|size:8',
        ]);

        // Find task by invite code (can be group or personal with share link)
        $task = Task::where('invite_code', strtoupper($request->invite_code))
            ->first();

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid invite code.'
            ], 404);
        }

        $user = Auth::user();

        // If task is still personal, convert it to group
        if ($task->type === 'personal') {
            $task->update([
                'type' => 'group',
                'task_list_id' => null, // Remove from task list
            ]);

            // Create assignment for creator as owner if not exists
            TaskAssignment::firstOrCreate(
                [
                    'task_id' => $task->id,
                    'user_id' => $task->created_by,
                ],
                [
                    'role' => 'owner',
                ]
            );
        }

        // Check if user is already assigned
        if ($task->isAssignedTo($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are already a member of this task.'
            ], 400);
        }

        // Check if there's a pending invitation for this user
        $invitation = TaskInvitation::where('task_id', $task->id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($invitation) {
            // Accept the existing invitation
            $invitation->update(['status' => 'accepted']);
        } else {
            // Create new invitation and auto-accept
            $invitation = TaskInvitation::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'invited_by' => $task->created_by,
                'token' => TaskInvitation::generateToken(),
                'role' => 'viewer',
                'status' => 'accepted',
            ]);
        }

        // Add user to task
        TaskAssignment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'role' => $invitation->role,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully joined the task.',
            'task' => $task->load(['creator', 'assignments.user'])
        ]);
    }

    /**
     * Join group task by invite link (token)
     * Also handles personal tasks - converts them to group when someone joins
     * Requires authentication
     */
    public function joinByLink(Request $request, $token)
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return redirect('/login')
                ->with('error', 'Please log in to join this task.')
                ->with('redirect', url('/admin/tasks/join-by-link/' . $token));
        }

        $invitation = TaskInvitation::where('token', $token)
            ->where('status', 'pending')
            ->first();

        if (!$invitation || !$invitation->isValid()) {
            return redirect('/admin/tasks?type=group')
                ->with('error', 'Invalid or expired invitation link.');
        }

        $user = Auth::user();
        $task = $invitation->task;

        // If invitation is for a specific user, check if it matches
        if ($invitation->user_id && $invitation->user_id !== $user->id) {
            return redirect('/admin/tasks?type=group')
                ->with('error', 'This invitation is for another user.');
        }

        // If task is still personal, convert it to group
        if ($task->type === 'personal') {
            $task->update([
                'type' => 'group',
                'task_list_id' => null, // Remove from task list
            ]);

            // Create assignment for creator as owner if not exists
            TaskAssignment::firstOrCreate(
                [
                    'task_id' => $task->id,
                    'user_id' => $task->created_by,
                ],
                [
                    'role' => 'owner',
                ]
            );

            // Generate invite code for the newly converted group task
            $task->generateInviteCode();
        }

        // Check if user is already assigned
        if ($task->isAssignedTo($user->id)) {
            return redirect('/admin/tasks?type=group')
                ->with('info', 'You are already a member of this task.');
        }

        // Accept invitation
        $invitation->update([
            'status' => 'accepted',
            'user_id' => $user->id, // Set user_id if it was a public invitation
        ]);

        // Add user to task
        TaskAssignment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'role' => $invitation->role,
        ]);

        return redirect('/admin/tasks?type=group')
            ->with('success', 'Successfully joined the task: ' . $task->title);
    }

    /**
     * Manually invite users to group task
     */
    public function inviteUsers(Request $request, Task $task)
    {
        if ($task->type !== 'group') {
            abort(400, 'Only group tasks can have invitations.');
        }

        // Check permission - only owner can invite
        $assignment = TaskAssignment::where('task_id', $task->id)
            ->where('user_id', Auth::id())
            ->first();
        if (!$assignment || $assignment->role !== 'owner') {
            abort(403, 'Only task owners can invite users.');
        }

        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'role' => 'required|in:assignee,viewer',
        ]);

        $invitedCount = 0;
        foreach ($request->user_ids as $userId) {
            // Skip if user is already assigned
            if ($task->isAssignedTo($userId)) {
                continue;
            }

            // Check if there's already a pending invitation
            $existingInvitation = TaskInvitation::where('task_id', $task->id)
                ->where('user_id', $userId)
                ->where('status', 'pending')
                ->first();

            if (!$existingInvitation) {
                TaskInvitation::create([
                    'task_id' => $task->id,
                    'user_id' => $userId,
                    'invited_by' => Auth::id(),
                    'token' => TaskInvitation::generateToken(),
                    'role' => $request->role,
                    'status' => 'pending',
                ]);
                $invitedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$invitedCount} invitation(s) sent successfully.",
            'invitations' => TaskInvitation::where('task_id', $task->id)
                ->where('status', 'pending')
                ->with('user')
                ->get()
        ]);
    }

    /**
     * Accept invitation
     */
    public function acceptInvitation(TaskInvitation $invitation)
    {
        if ($invitation->user_id !== Auth::id()) {
            abort(403, 'This invitation is not for you.');
        }

        if (!$invitation->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired invitation.'
            ], 400);
        }

        $task = $invitation->task;

        // Check if user is already assigned
        if ($task->isAssignedTo(Auth::id())) {
            $invitation->update(['status' => 'accepted']);
            return response()->json([
                'success' => false,
                'message' => 'You are already a member of this task.'
            ], 400);
        }

        // Accept invitation
        $invitation->update(['status' => 'accepted']);

        // Add user to task
        TaskAssignment::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'role' => $invitation->role,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Invitation accepted successfully.',
            'task' => $task->load(['creator', 'assignments.user'])
        ]);
    }

    /**
     * Reject invitation
     */
    public function rejectInvitation(TaskInvitation $invitation)
    {
        if ($invitation->user_id !== Auth::id()) {
            abort(403, 'This invitation is not for you.');
        }

        $invitation->update(['status' => 'rejected']);

        return response()->json([
            'success' => true,
            'message' => 'Invitation rejected.'
        ]);
    }

    /**
     * Get pending invitations for current user
     */
    public function getPendingInvitations()
    {
        $invitations = TaskInvitation::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->with(['task.creator', 'inviter'])
            ->get();

        return response()->json([
            'success' => true,
            'invitations' => $invitations
        ]);
    }

    /**
     * Convert personal task to group task and share it
     */
    public function convertToGroup(Request $request, Task $task)
    {
        // Check permission - only creator can convert
        if ($task->type !== 'personal' || $task->created_by !== Auth::id()) {
            abort(403, 'Only personal task creators can convert tasks to group tasks.');
        }

        $request->validate([
            'share_method' => 'required|in:code,link,manual',
            'user_ids' => 'required_if:share_method,manual|array',
            'user_ids.*' => 'exists:users,id',
            'role' => 'required_if:share_method,manual|in:assignee,viewer',
        ]);

        $user = Auth::user();

        // Convert task to group
        $task->update([
            'type' => 'group',
            'task_list_id' => null, // Remove from task list
        ]);

        // Create assignment for creator as owner
        TaskAssignment::firstOrCreate(
            [
                'task_id' => $task->id,
                'user_id' => $user->id,
            ],
            [
                'role' => 'owner',
            ]
        );

        // Generate invite code
        $inviteCode = $task->generateInviteCode();

        $responseData = [
            'success' => true,
            'message' => 'Task converted to group task successfully.',
            'invite_code' => $inviteCode,
            'task' => $task->load(['creator', 'assignments.user']),
        ];

        // Handle sharing method
        if ($request->share_method === 'link') {
            // Create or get existing invitation token
            $invitation = TaskInvitation::where('task_id', $task->id)
                ->where('status', 'pending')
                ->whereNull('user_id') // Public invitation
                ->first();

            if (!$invitation) {
                $invitation = TaskInvitation::create([
                    'task_id' => $task->id,
                    'user_id' => null, // Public invitation
                    'invited_by' => $user->id,
                    'token' => TaskInvitation::generateToken(),
                    'role' => 'viewer',
                    'status' => 'pending',
                ]);
            }

            $responseData['invite_link'] = url('/admin/tasks/join-by-link/' . $invitation->token);
            $responseData['token'] = $invitation->token;
        } elseif ($request->share_method === 'manual') {
            // Create invitations for selected users
            $invitedCount = 0;
            foreach ($request->user_ids as $userId) {
                // Skip if user is already assigned (shouldn't happen, but safety check)
                if ($task->isAssignedTo($userId)) {
                    continue;
                }

                // Check if there's already a pending invitation
                $existingInvitation = TaskInvitation::where('task_id', $task->id)
                    ->where('user_id', $userId)
                    ->where('status', 'pending')
                    ->first();

                if (!$existingInvitation) {
                    TaskInvitation::create([
                        'task_id' => $task->id,
                        'user_id' => $userId,
                        'invited_by' => $user->id,
                        'token' => TaskInvitation::generateToken(),
                        'role' => $request->role,
                        'status' => 'pending',
                    ]);
                    $invitedCount++;
                }
            }

            $responseData['invited_count'] = $invitedCount;
            $responseData['message'] = "Task converted to group task and {$invitedCount} invitation(s) sent successfully.";
        }

        return response()->json($responseData);
    }

    /**
     * Log task activity
     */
    private function logTaskActivity(Task $task, string $action, ?string $field = null, $oldValue = null, $newValue = null, ?string $description = null, array $metadata = [])
    {
        TaskActivityLog::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'action' => $action,
            'field' => $field,
            'old_value' => $oldValue ? (is_string($oldValue) ? $oldValue : json_encode($oldValue)) : null,
            'new_value' => $newValue ? (is_string($newValue) ? $newValue : json_encode($newValue)) : null,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}
