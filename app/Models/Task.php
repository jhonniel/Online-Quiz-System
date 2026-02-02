<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'notes',
        'type',
        'status',
        'priority',
        'order',
        'created_by',
        'parent_id',
        'task_list_id',
        'due_date',
        'invite_code',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'order' => 'integer',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignments()
    {
        return $this->hasMany(TaskAssignment::class);
    }

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'task_assignments')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class)->orderBy('created_at', 'asc');
    }

    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function parent()
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function subtasks()
    {
        return $this->hasMany(Task::class, 'parent_id')->orderBy('order');
    }

    public function taskList()
    {
        return $this->belongsTo(TaskList::class);
    }

    public function invitations()
    {
        return $this->hasMany(TaskInvitation::class);
    }

    // Scopes
    public function scopePersonal($query)
    {
        return $query->where('type', 'personal');
    }

    public function scopeGroup($query)
    {
        return $query->where('type', 'group');
    }

    public function scopeTodo($query)
    {
        return $query->where('status', 'todo');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeDone($query)
    {
        return $query->where('status', 'done');
    }

    public function scopeParentTasks($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeSubtasks($query)
    {
        return $query->whereNotNull('parent_id');
    }

    // Helper methods
    public function isAssignedTo($userId)
    {
        return $this->assignments()->where('user_id', $userId)->exists();
    }

    public function canView($userId)
    {
        if ($this->type === 'personal') {
            return $this->created_by === $userId || $this->assigned_to === $userId;
        }
        
        return $this->assignments()->where('user_id', $userId)->exists() 
            || $this->created_by === $userId;
    }

    public function generateInviteCode()
    {
        if (!$this->invite_code) {
            do {
                $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
            } while (Task::where('invite_code', $code)->exists());
            
            $this->update(['invite_code' => $code]);
        }
        
        return $this->invite_code;
    }
}
