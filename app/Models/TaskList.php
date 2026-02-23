<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaskList extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'color',
        'order',
        'invite_code',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class)->whereNull('parent_id'); // Only parent tasks
    }

    public function invitations()
    {
        return $this->hasMany(TaskListInvitation::class);
    }

    public function sharedWith()
    {
        return $this->belongsToMany(User::class, 'task_list_invitations')
                    ->wherePivot('status', 'accepted')
                    ->withPivot('invited_by', 'created_at')
                    ->withTimestamps();
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId)->orderBy('order');
    }

    public function scopeSharedWithUser($query, $userId)
    {
        return $query->whereHas('invitations', function($q) use ($userId) {
            $q->where('user_id', $userId)
              ->where('status', 'accepted');
        });
    }

    public function generateInviteCode()
    {
        if (!$this->invite_code) {
            do {
                $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
            } while (TaskList::where('invite_code', $code)->exists());
            
            $this->update(['invite_code' => $code]);
        }
        
        return $this->invite_code;
    }

    public function isOwner($userId)
    {
        return $this->user_id === $userId;
    }

    public function isSharedWith($userId)
    {
        return $this->invitations()
            ->where('user_id', $userId)
            ->where('status', 'accepted')
            ->exists();
    }

    /**
     * Compatibility: some Laravel versions call hasAnyGetMutator(); this model has no get mutators.
     */
    public function hasAnyGetMutator($key = null): bool
    {
        if ($key !== null && $key !== '') {
            return $this->hasGetMutator($key);
        }
        return false;
    }
}
