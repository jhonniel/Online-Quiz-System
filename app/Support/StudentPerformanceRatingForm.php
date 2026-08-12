<?php

namespace App\Support;

use App\Models\User;

/**
 * Affiliate agency / OJT performance rating rubric (1–5 items + grand-total extras).
 */
class StudentPerformanceRatingForm
{
    public const SCORE_MIN = 1;

    public const SCORE_MAX = 5;

    /**
     * @return list<array{key: string, title: string, items: list<array{field: string, label: string, max: int}>}>
     */
    public static function sections(): array
    {
        return [
            [
                'key' => 'leadership',
                'title' => 'Leadership',
                'items' => [
                    [
                        'field' => 'leadership_self_discipline',
                        'label' => 'Has self-discipline and potential for leadership',
                        'max' => self::SCORE_MAX,
                    ],
                    [
                        'field' => 'leadership_responsibility',
                        'label' => 'Assumes responsibility readily, gets results and group loyalty',
                        'max' => self::SCORE_MAX,
                    ],
                    [
                        'field' => 'leadership_understands_instructions',
                        'label' => 'Able to understand clear instructions and does not need constant supervision',
                        'max' => self::SCORE_MAX,
                    ],
                    [
                        'field' => 'leadership_accepts_suggestions',
                        'label' => 'Accepts suggestions and strives to improve his work.',
                        'max' => self::SCORE_MAX,
                    ],
                ],
            ],
            [
                'key' => 'attitude',
                'title' => 'Attitude Towards Work',
                'items' => [
                    [
                        'field' => 'attitude_time_use',
                        'label' => 'Makes use of time and not squanders it.',
                        'max' => self::SCORE_MAX,
                    ],
                    [
                        'field' => 'attitude_punctuality',
                        'label' => 'Reports to work regularly on time.',
                        'max' => self::SCORE_MAX,
                    ],
                    [
                        'field' => 'attitude_follows_rules',
                        'label' => 'Follows company/agency rules and regulations.',
                        'max' => self::SCORE_MAX,
                    ],
                    [
                        'field' => 'attitude_courteous',
                        'label' => 'Courteous/polite.',
                        'max' => self::SCORE_MAX,
                    ],
                ],
            ],
            [
                'key' => 'performance',
                'title' => 'Performance',
                'items' => [
                    [
                        'field' => 'performance_accuracy',
                        'label' => 'Works accurately, efficiently and effectively.',
                        'max' => self::SCORE_MAX,
                    ],
                    [
                        'field' => 'performance_timely_tasks',
                        'label' => 'Accomplishes assigned tasks on time.',
                        'max' => self::SCORE_MAX,
                    ],
                    [
                        'field' => 'performance_follows_directions',
                        'label' => 'Follows directions/instructions correctly.',
                        'max' => self::SCORE_MAX,
                    ],
                    [
                        'field' => 'performance_quality_cooperation',
                        'label' => 'Produces quality work and shows cooperation with others.',
                        'max' => self::SCORE_MAX,
                    ],
                ],
            ],
            [
                'key' => 'grand_total',
                'title' => "Grand Total for Affiliate Agency's Rating",
                'items' => [
                    [
                        'field' => 'learning_journal_evaluation',
                        'label' => 'Learning Experience Journal Evaluation',
                        'max' => 10,
                    ],
                    [
                        'field' => 'requirements_assessment',
                        'label' => 'Assessment of the Requirements submitted to the OJT Placement Office',
                        'max' => 30,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, int> field => max score
     */
    public static function fieldMaxScores(): array
    {
        $max = [];
        foreach (self::sections() as $section) {
            foreach ($section['items'] as $item) {
                $max[$item['field']] = (int) $item['max'];
            }
        }

        return $max;
    }

    /**
     * @return list<string>
     */
    public static function scoreFields(): array
    {
        return array_keys(self::fieldMaxScores());
    }

    /**
     * @return array<string, array{label: string, min: int, max: int}>
     */
    public static function scoreFieldConstraints(): array
    {
        $constraints = [];
        foreach (self::sections() as $section) {
            $min = $section['key'] === 'grand_total' ? 0 : self::SCORE_MIN;
            foreach ($section['items'] as $item) {
                $constraints[$item['field']] = [
                    'label' => $item['label'],
                    'min' => $min,
                    'max' => (int) $item['max'],
                ];
            }
        }

        return $constraints;
    }

    /**
     * @return array<string, mixed>
     */
    public static function validationRules(): array
    {
        $rules = [
            'notes' => ['nullable', 'string', 'max:5000'],
        ];

        foreach (self::scoreFieldConstraints() as $field => $constraint) {
            $rules[$field] = [
                'required',
                'integer',
                'min:'.$constraint['min'],
                'max:'.$constraint['max'],
                'regex:/^\d+$/',
            ];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public static function validationMessages(): array
    {
        $messages = [];

        foreach (self::scoreFieldConstraints() as $field => $constraint) {
            $label = $constraint['label'];
            $min = $constraint['min'];
            $max = $constraint['max'];
            $range = "a whole number from {$min} to {$max}";

            $messages[$field.'.required'] = "Enter a score for {$label}.";
            $messages[$field.'.integer'] = "{$label} must be {$range}.";
            $messages[$field.'.min'] = "{$label} must be at least {$min}.";
            $messages[$field.'.max'] = "{$label} cannot be more than {$max}.";
            $messages[$field.'.regex'] = "{$label} must be {$range}.";
        }

        return $messages;
    }

    public static function viewerCanAccess(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->canAccessStudentFeature('student_performance_ratings');
    }

    public static function canAccessStudentsListForRating(?User $user): bool
    {
        if (! $user || ! $user->canAccessStudentManagement()) {
            return false;
        }

        return $user->canAccessStudentFeature('students')
            || $user->canAccessStudentFeature('student_performance_ratings');
    }

    /**
     * @param  array<string, mixed>|object  $values
     * @return array{sections: array<string, int>, rubric: int, grand_total_extras: int, overall: int, overall_max: int}
     */
    public static function totals(array|object $values): array
    {
        $get = static function (string $field) use ($values): int {
            if (is_array($values)) {
                return (int) ($values[$field] ?? 0);
            }

            return (int) ($values->{$field} ?? 0);
        };

        $sectionTotals = [];
        $rubric = 0;
        $extras = 0;
        $overallMax = 0;

        foreach (self::sections() as $section) {
            $sum = 0;
            foreach ($section['items'] as $item) {
                $sum += $get($item['field']);
                $overallMax += (int) $item['max'];
            }
            $sectionTotals[$section['key']] = $sum;
            if ($section['key'] === 'grand_total') {
                $extras = $sum;
            } else {
                $rubric += $sum;
            }
        }

        return [
            'sections' => $sectionTotals,
            'rubric' => $rubric,
            'grand_total_extras' => $extras,
            'overall' => $rubric + $extras,
            'overall_max' => $overallMax,
        ];
    }
}
