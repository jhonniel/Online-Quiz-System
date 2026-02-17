<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomBoard extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'task_list_id',
        'name',
        'status_key',
        'color',
        'order',
        'is_default',
        'is_locked',
    ];

    protected $casts = [
        'order' => 'integer',
        'is_default' => 'boolean',
        'is_locked' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function taskList()
    {
        return $this->belongsTo(TaskList::class);
    }

    // Scopes
    public function scopeForUser($query, $userId, $type, $taskListId = null)
    {
        $query = $query->where('user_id', $userId)
                       ->where('type', $type);
        
        if ($taskListId !== null) {
            $query->where('task_list_id', $taskListId);
        } else {
            $query->whereNull('task_list_id');
        }
        
        return $query->orderBy('order');
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeCustom($query)
    {
        return $query->where('is_default', false);
    }
}
