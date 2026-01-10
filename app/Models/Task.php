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
        'type',
        'status',
        'order',
        'created_by',
        'due_date',
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
}
