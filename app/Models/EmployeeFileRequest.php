<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeFileRequest extends Model
{
    protected $fillable = [
        'employee_file_template_id',
        'user_id',
        'generated_by',
        'title',
        'original_filename',
        'mime_type',
        'field_values',
        'rendered_html',
        'pdf_path',
    ];

    protected $casts = [
        'field_values' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmployeeFileTemplate::class, 'employee_file_template_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
