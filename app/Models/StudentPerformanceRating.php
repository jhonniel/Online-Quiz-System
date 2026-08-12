<?php

namespace App\Models;

use App\Support\StudentPerformanceRatingForm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPerformanceRating extends Model
{
    protected $fillable = [
        'user_id',
        'rated_by',
        'rated_by_name',
        'rated_by_email',
        'rated_at',
        'leadership_self_discipline',
        'leadership_responsibility',
        'leadership_understands_instructions',
        'leadership_accepts_suggestions',
        'attitude_time_use',
        'attitude_punctuality',
        'attitude_follows_rules',
        'attitude_courteous',
        'performance_accuracy',
        'performance_timely_tasks',
        'performance_follows_directions',
        'performance_quality_cooperation',
        'learning_journal_evaluation',
        'requirements_assessment',
        'notes',
    ];

    protected $casts = [
        'rated_at' => 'datetime',
        'leadership_self_discipline' => 'integer',
        'leadership_responsibility' => 'integer',
        'leadership_understands_instructions' => 'integer',
        'leadership_accepts_suggestions' => 'integer',
        'attitude_time_use' => 'integer',
        'attitude_punctuality' => 'integer',
        'attitude_follows_rules' => 'integer',
        'attitude_courteous' => 'integer',
        'performance_accuracy' => 'integer',
        'performance_timely_tasks' => 'integer',
        'performance_follows_directions' => 'integer',
        'performance_quality_cooperation' => 'integer',
        'learning_journal_evaluation' => 'integer',
        'requirements_assessment' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rated_by');
    }

    public function raterDisplayName(): string
    {
        if (! empty($this->rated_by_name)) {
            return (string) $this->rated_by_name;
        }

        return (string) (optional($this->rater)->name ?? 'Unknown rater');
    }

    public function raterDisplayEmail(): ?string
    {
        if (! empty($this->rated_by_email)) {
            return (string) $this->rated_by_email;
        }

        $email = optional($this->rater)->email;

        return $email ? (string) $email : null;
    }

    /**
     * @return array{sections: array<string, int>, rubric: int, grand_total_extras: int, overall: int, overall_max: int}
     */
    public function totals(): array
    {
        return StudentPerformanceRatingForm::totals($this);
    }

    public function overallScore(): int
    {
        return (int) ($this->totals()['overall'] ?? 0);
    }

    public function overallMax(): int
    {
        return (int) ($this->totals()['overall_max'] ?? 0);
    }

    public function isComplete(): bool
    {
        foreach (StudentPerformanceRatingForm::scoreFields() as $field) {
            if ($this->{$field} === null) {
                return false;
            }
        }

        return true;
    }
}
