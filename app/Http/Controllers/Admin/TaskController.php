<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\TaskComment;
use App\Models\TaskAttachment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks (My Tasks or Group Tasks)
     */
    public function index(Request $request)
    {
        $type = $request->get('type', 'personal'); // 'personal' or 'group'
        $view = $request->get('view', 'board'); // 'board' or 'list'
        
        $user = Auth::user();
        
        if ($type === 'personal') {
            // My Tasks - tasks created by the user
            $tasks = Task::where('type', 'personal')
                ->where('created_by', $user->id)
                ->with(['creator', 'attachments', 'comments.user'])
                ->orderBy('order')
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Group Tasks - tasks where user is assigned or created
            $taskIds = TaskAssignment::where('user_id', $user->id)
                ->pluck('task_id')
                ->toArray();
            
            $tasks = Task::where('type', 'group')
                ->where(function($query) use ($user, $taskIds) {
                    $query->where('created_by', $user->id)
                          ->orWhereIn('id', $taskIds);
                })
                ->with(['creator', 'assignments.user', 'attachments', 'comments.user'])
                ->orderBy('order')
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        // Group tasks by status
        $tasksByStatus = [
            'todo' => $tasks->where('status', 'todo')->values(),
            'in_progress' => $tasks->where('status', 'in_progress')->values(),
            'done' => $tasks->where('status', 'done')->values(),
        ];
        
        // Get all users for group task assignment
        $users = User::where('is_active', true)
            ->where('id', '!=', $user->id)
            ->orderBy('name')
            ->get();
        
        // Format tasks for JSON (for JavaScript)
        $tasksJson = $tasks->map(function($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'type' => $task->type,
                'due_date' => $task->due_date ? $task->due_date->toISOString() : null,
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
        })->toArray();
        
        return view('admin.tasks.index', compact('tasks', 'tasksByStatus', 'type', 'view', 'users', 'tasksJson'));
    }

    /**
     * Store a newly created task
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'type' => 'required|in:personal,group',
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

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'status' => 'todo',
            'created_by' => $user->id,
            'due_date' => $dueDate ? date('Y-m-d H:i:s', strtotime($dueDate)) : null,
            'order' => $maxOrder + 1,
        ]);

        // For group tasks, assign the creator as owner
        if ($request->type === 'group') {
            TaskAssignment::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'role' => 'owner',
            ]);
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
            'status' => 'sometimes|in:todo,in_progress,done',
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
        
        if ($request->has('status')) {
            $updateData['status'] = $request->status;
            
            // Update order when status changes
            if ($request->status !== $task->status) {
                $maxOrder = Task::where('type', $task->type)
                    ->where('status', $request->status)
                    ->where('created_by', $task->created_by)
                    ->max('order') ?? 0;
                $updateData['order'] = $maxOrder + 1;
            }
        }
        
        if ($request->has('due_date')) {
            $dueDate = $request->due_date;
            if ($request->due_time) {
                $dueDate .= ' ' . $request->due_time . ':00';
            } elseif ($request->due_date && !$request->has('due_time')) {
                $dueDate .= ' 23:59:59';
            }
            $updateData['due_date'] = $dueDate ? date('Y-m-d H:i:s', strtotime($dueDate)) : null;
        }

        $task->update($updateData);
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
            Task::where('id', $taskData['id'])
                ->update([
                    'status' => $taskData['status'],
                    'order' => $taskData['order'],
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Task order updated successfully.'
        ]);
    }

    /**
     * Delete a task
     */
    public function destroy(Task $task)
    {
        // Check permission
        if ($task->type === 'personal' && $task->created_by !== Auth::id()) {
            abort(403, 'You do not have permission to delete this task.');
        }
        
        if ($task->type === 'group') {
            $assignment = TaskAssignment::where('task_id', $task->id)
                ->where('user_id', Auth::id())
                ->first();
            if (!$assignment || $assignment->role !== 'owner') {
                abort(403, 'Only task owners can delete tasks.');
            }
        }

        // Delete attachments from storage
        foreach ($task->attachments as $attachment) {
            try {
                if (Storage::disk('digitalocean')->exists($attachment->file_path)) {
                    Storage::disk('digitalocean')->delete($attachment->file_path);
                }
            } catch (\Exception $e) {
                // Ignore deletion errors
            }
        }

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

        // Check permission
        if ($task->type === 'personal' && $task->created_by !== Auth::id()) {
            abort(403, 'You do not have permission to comment on this task.');
        }
        
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
        $assetDisk = 'digitalocean';
        $assetRoot = trim(env('DIGITALOCEAN_SPACES_ROOT_PATH', ''), '/');
        $taskDir = $assetRoot ? $assetRoot . '/tasks/' . $task->id : 'tasks/' . $task->id;
        
        $filePath = $file->store($taskDir, $assetDisk);

        $attachment = TaskAttachment::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
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
            if (Storage::disk('digitalocean')->exists($attachment->file_path)) {
                Storage::disk('digitalocean')->delete($attachment->file_path);
            }
        } catch (\Exception $e) {
            // Ignore deletion errors
        }

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
}
