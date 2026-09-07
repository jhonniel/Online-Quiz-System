<?php

namespace App\Models;

use App\Support\DtrTimeRequestHours;
use App\Support\TimeRequestOvertimeLeaveImport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    protected $fillable = [
        'user_id',
        'dtr_time_request_id',
        'attendance_submission_batch',
        'teacher_excused_batch',
        'attendance_overtime_completed_at',
        'type',
        'start_date',
        'end_date',
        'reason',
        'travel_hours',
        'supporting_document_path',
        'supporting_document_paths',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
        'admin_officially_excused',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'reviewed_at' => 'datetime',
        'attendance_overtime_completed_at' => 'datetime',
        'travel_hours' => 'float',
        'supporting_document_paths' => 'array',
        'admin_officially_excused' => 'boolean',
    ];

    /**
     * Normalized list of supporting document paths (new multi-upload + legacy single path).
     *
     * @return list<string>
     */
    public function getAllSupportingDocumentPathsAttribute(): array
    {
        $paths = collect($this->supporting_document_paths ?? [])
            ->map(fn ($path) => trim((string) $path))
            ->filter()
            ->values();

        if ($paths->isNotEmpty()) {
            return $paths->all();
        }

        $legacy = trim((string) ($this->supporting_document_path ?? ''));

        return $legacy !== '' ? [$legacy] : [];
    }

    /**
     * Get the user who created this leave request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dtrTimeRequest(): BelongsTo
    {
        return $this->belongsTo(DtrTimeRequest::class);
    }

    /**
     * Get the admin who reviewed this leave request.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get all logs for this leave request.
     */
    public function logs()
    {
        return $this->hasMany(LeaveRequestLog::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the log entry for approval (most recent).
     */
    public function approvedBy()
    {
        return $this->hasOne(LeaveRequestLog::class)
            ->where('action', 'approved')
            ->latest();
    }

    /**
     * Get the log entry for rejection (most recent).
     */
    public function rejectedBy()
    {
        return $this->hasOne(LeaveRequestLog::class)
            ->where('action', 'rejected')
            ->latest();
    }

    /**
     * Get the log entry for resubmission request (most recent).
     */
    public function resubmissionRequestedBy()
    {
        return $this->hasOne(LeaveRequestLog::class)
            ->where('action', 'resubmission_requested')
            ->latest();
    }

    /**
     * Leave types an admin may assign when correcting a request (by requester role).
     *
     * @return list<string>
     */
    public static function adminSelectableTypesForRole(string $role): array
    {
        if ($role === 'student') {
            return ['additional_time', 'absent', 'excused', 'overtime', 'other'];
        }

        return [
            'leave',
            'vacation_leave',
            'sick_leave',
            'work_from_home',
            'absent',
            'overtime',
            'offset',
            'additional_time',
            'travel',
            'other',
        ];
    }

    /**
     * Human-readable label for a stored type value.
     */
    public static function labelForType(?string $type): string
    {
        if ($type === null || $type === '') {
            return 'Unknown';
        }

        return self::typeLabelMap()[$type] ?? ucfirst(str_replace('_', ' ', $type));
    }

    /**
     * Leave types HR users may view on admin employee leave requests.
     *
     * @return list<string>
     */
    public static function hrViewableTypes(): array
    {
        return ['vacation_leave', 'sick_leave'];
    }

    public static function isHrViewableType(?string $type): bool
    {
        return in_array($type, self::hrViewableTypes(), true);
    }

    /**
     * Employee overtime subtypes (Office Work vs Travel Time).
     *
     * @return array<string, string>
     */
    public static function overtimeWorkTypes(): array
    {
        return [
            'office_work' => 'Office Work',
            'travel_time' => 'Travel Time',
        ];
    }

    public const OVERTIME_LOOKBACK_DAYS_DEFAULT = 7;

    public const OVERTIME_LOOKBACK_DAYS_TRAVEL_TIME = 30;

    public static function overtimeLookbackDays(?string $workType): int
    {
        return $workType === 'travel_time'
            ? self::OVERTIME_LOOKBACK_DAYS_TRAVEL_TIME
            : self::OVERTIME_LOOKBACK_DAYS_DEFAULT;
    }

    public static function overtimeWorkTypeLabel(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::overtimeWorkTypes()[$value] ?? null;
    }

    /**
     * Parse overtime subtype from stored reason text.
     */
    public static function parseOvertimeWorkTypeFromReason(?string $reason): ?string
    {
        if ($reason === null || $reason === '') {
            return null;
        }

        if (! preg_match('/Overtime Type:\s*(.+)/i', $reason, $m)) {
            return null;
        }

        $label = strtolower(trim($m[1]));

        return match ($label) {
            'office work' => 'office_work',
            'travel time' => 'travel_time',
            default => null,
        };
    }

    /**
     * Human-readable overtime subtype for this request, if present.
     */
    public function overtimeWorkTypeLabelFromReason(): ?string
    {
        $value = self::parseOvertimeWorkTypeFromReason($this->reason);

        return self::overtimeWorkTypeLabel($value);
    }

    public static function travelTimeLocationReasonLine(TravelTimeLocation $location): string
    {
        return sprintf(
            'Travel Location: %s — %s (%s)',
            $location->name,
            $location->hoursFormatted(),
            $location->tripTypeLabel()
        );
    }

    public static function parseTravelTimeLocationIdFromReason(?string $reason): ?int
    {
        if ($reason === null || $reason === '') {
            return null;
        }

        if (! preg_match('/Travel Location ID:\s*(\d+)/i', $reason, $m)) {
            return null;
        }

        return (int) $m[1];
    }

    public static function parseTravelTimeLocationSummaryFromReason(?string $reason): ?string
    {
        if ($reason === null || $reason === '') {
            return null;
        }

        if (! preg_match('/Travel Location:\s*(.+)/i', $reason, $m)) {
            return null;
        }

        return trim($m[1]);
    }

    public function travelTimeLocationSummaryFromReason(): ?string
    {
        $id = self::parseTravelTimeLocationIdFromReason($this->reason);
        if ($id !== null) {
            $location = TravelTimeLocation::find($id);
            if ($location !== null) {
                return sprintf(
                    '%s — %s (%s)',
                    $location->name,
                    $location->hoursFormatted(),
                    $location->tripTypeLabel()
                );
            }
        }

        return self::parseTravelTimeLocationSummaryFromReason($this->reason);
    }

    /**
     * Dates shown in overtime/additional-time request letters.
     */
    public function overtimeDisplayDatesForLetter(): string
    {
        if (self::parseOvertimeWorkTypeFromReason($this->reason) === 'travel_time' && $this->start_date) {
            $start = $this->start_date->format('Y-m-d');
            $end = ($this->end_date ?? $this->start_date)->format('Y-m-d');

            return $start === $end ? $start : "{$start} to {$end}";
        }

        $raw = $this->reason ?? '';
        if (preg_match('/(?:Overtime|Additional Time) Dates:\s*(.+)/i', $raw, $m)) {
            return trim($m[1]);
        }

        return '';
    }

    public static function parseGrossOvertimeHoursFromReason(?string $reason): ?string
    {
        if ($reason === null || $reason === '') {
            return null;
        }

        if (! preg_match('/Gross (?:Overtime|Additional Time) Hours:\s*(.+)/i', $reason, $m)) {
            return null;
        }

        return trim($m[1]);
    }

    public static function parseTravelTimeDeductedFromReason(?string $reason): ?string
    {
        if ($reason === null || $reason === '') {
            return null;
        }

        if (! preg_match('/Travel Time Deducted:\s*(.+)/i', $reason, $m)) {
            return null;
        }

        return trim($m[1]);
    }

    /**
     * Get the type label.
     */
    public function getTypeLabelAttribute(): string
    {
        if ($this->type === 'excused') {
            return $this->wasFiledByTeacher() ? 'Official Excused' : 'Excused';
        }

        if ($this->type === 'absent' && $this->wasFiledByTeacher()) {
            return 'Official Excused';
        }

        $isStudentRequest = ($this->user?->role === 'student')
            || (auth()->check() && auth()->user()->role === 'student');

        if ($this->type === 'overtime' && $isStudentRequest) {
            return 'Additional Time';
        }

        return self::labelForType($this->type);
    }

    /**
     * @return array<string, string>
     */
    private static function typeLabelMap(): array
    {
        return [
            'leave' => 'Leave',
            'vacation_leave' => 'Vacation Leave',
            'sick_leave' => 'Sick Leave',
            'work_from_home' => 'Work From Home',
            'absent' => 'Absent',
            'excused' => 'Excused',
            'overtime' => 'Overtime',
            'offset' => 'Offset',
            'additional_time' => 'Additional Time',
            'travel' => 'Travel',
            'other' => 'Other',
        ];
    }

    /**
     * Get the status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        if ($this->needsAttendanceOvertimeCompletion()) {
            return 'bg-orange-100 text-orange-800';
        }

        return match ($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'for_more_verification' => 'bg-blue-100 text-blue-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get a human-readable status label.
     *
     * If the request is pending but has already been reviewed (admin requested
     * changes via resubmission), show \"Resubmission\" as the status label.
     */
    public function getDisplayStatusAttribute(): string
    {
        if ($this->needsAttendanceOvertimeCompletion()) {
            return 'Incomplete details';
        }

        if ($this->status === 'pending' && $this->reviewed_at) {
            return 'Resubmission';
        }
        if ($this->status === 'for_more_verification') {
            return 'For More Verification';
        }

        return ucfirst($this->status);
    }

    public function needsAttendanceOvertimeCompletion(): bool
    {
        return $this->type === 'overtime'
            && $this->isPending()
            && filled($this->attendance_submission_batch)
            && $this->attendance_overtime_completed_at === null;
    }

    /**
     * Total hours the student filed for the day via Record Attendance (HH:MM), for admin review.
     */
    public function attendanceDayTotalFiledDisplay(): ?string
    {
        $hours = null;

        $this->loadMissing('dtrTimeRequest');

        if ($this->dtrTimeRequest?->requested_total_hours) {
            $hours = (float) $this->dtrTimeRequest->requested_total_hours;
        }

        if ($hours === null) {
            $hours = TimeRequestOvertimeLeaveImport::parseDayTotalHoursFromReason($this->reason);
        }

        if ($hours === null || $hours <= 0) {
            return null;
        }

        return DtrTimeRequestHours::decimalToTimeString($hours);
    }

    public function isAttendanceOvertimeReadyForApproval(): bool
    {
        if (! TimeRequestOvertimeLeaveImport::isFiledFromAttendance($this)) {
            return true;
        }

        return $this->attendance_overtime_completed_at !== null;
    }

    /**
     * Check if the request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the request is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the request is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Pending requests where the administrator asked the user to fix and resubmit.
     */
    public function scopeAwaitingUserResubmission(Builder $query): Builder
    {
        return $query->where('status', 'pending')
            ->whereNotNull('reviewed_at')
            ->whereHas('logs', function ($q): void {
                $q->where('action', 'resubmission_requested');
            });
    }

    /**
     * Pending overtime from Record Attendance awaiting student completion form.
     */
    public function scopeAwaitingAttendanceOvertimeCompletion(Builder $query): Builder
    {
        return $query->where('type', 'overtime')
            ->where('status', 'pending')
            ->whereNotNull('attendance_submission_batch')
            ->whereNull('attendance_overtime_completed_at');
    }

    /**
     * Get the number of days.
     */
    public function getDaysAttribute(): int
    {
        if (! $this->end_date) {
            return 1;
        }

        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Minutes from "Total Overtime Hours: H:MM" in stored reason (overtime), or null if missing/invalid.
     */
    public function parseOvertimeTotalMinutesFromReason(): ?int
    {
        $raw = (string) ($this->reason ?? '');
        if (! preg_match('/Total Overtime Hours:\s*(\d{1,4}):(\d{2})/', $raw, $m)) {
            return null;
        }
        $h = (int) $m[1];
        $min = (int) $m[2];
        if ($min > 59) {
            return null;
        }
        $total = $h * 60 + $min;

        return $total >= 0 ? $total : null;
    }

    public function overtimeHoursFormattedFromReason(): ?string
    {
        $mins = $this->parseOvertimeTotalMinutesFromReason();
        if ($mins === null) {
            return null;
        }

        return sprintf('%02d:%02d', intdiv($mins, 60), $mins % 60);
    }

    public function replaceOvertimeTotalHoursInReason(string $hhmm): string
    {
        $raw = (string) ($this->reason ?? '');

        if (preg_match('/^(\d{1,4}):(\d{2})$/', trim($hhmm), $parts)) {
            $normalized = sprintf('%02d:%02d', (int) $parts[1], (int) $parts[2]);
        } else {
            $normalized = trim($hhmm);
        }

        if (preg_match('/Total Overtime Hours:\s*.+/m', $raw)) {
            return preg_replace(
                '/Total Overtime Hours:\s*.+/',
                'Total Overtime Hours: '.$normalized,
                $raw,
                1
            );
        }

        $raw = trim($raw);

        return $raw === ''
            ? 'Total Overtime Hours: '.$normalized
            : $raw."\nTotal Overtime Hours: ".$normalized;
    }

    /**
     * Total overtime hours as decimal (from reason text), for balance/DTR helpers.
     */
    public function getOvertimeHoursFromReasonAttribute(): float
    {
        $mins = $this->parseOvertimeTotalMinutesFromReason();

        return $mins !== null ? $mins / 60.0 : 0.0;
    }

    /**
     * Minutes from "Hours to Deduct: H:MM" in stored reason (offset), or null if missing/invalid.
     */
    public function parseOffsetHoursToDeductMinutes(): ?int
    {
        $raw = (string) ($this->reason ?? '');
        if (! preg_match('/Hours to Deduct:\s*(\d{1,4}):(\d{2})/', $raw, $m)) {
            return null;
        }
        $h = (int) $m[1];
        $min = (int) $m[2];
        if ($min > 59) {
            return null;
        }
        $total = $h * 60 + $min;

        return $total > 0 ? $total : null;
    }

    /**
     * Human-readable duration for details UI.
     * For offset: day count only in lists (dates live in start/end columns); custom HH:MM when hours differ from days × 8h.
     */
    public function getDurationDisplayLabelAttribute(): string
    {
        if ($this->type === 'overtime') {
            $mins = $this->parseOvertimeTotalMinutesFromReason();
            if ($mins !== null) {
                $h = intdiv($mins, 60);
                $m = $mins % 60;

                return sprintf('%02d:%02d', $h, $m);
            }

            $d = $this->days;

            return $d.' '.($d === 1 ? 'day' : 'days');
        }

        if ($this->type !== 'offset') {
            $d = $this->days;

            return $d.' '.($d === 1 ? 'day' : 'days');
        }

        $deductMins = $this->parseOffsetHoursToDeductMinutes();
        $days = $this->days;
        $defaultMins = $days * 8 * 60;

        if ($deductMins !== null && $deductMins !== $defaultMins) {
            $h = intdiv($deductMins, 60);
            $m = $deductMins % 60;

            return sprintf('%d:%02d', $h, $m).' (hours to deduct)';
        }

        return $days.' '.($days === 1 ? 'day' : 'days');
    }

    /**
     * Total offset hours to deduct (matches DTR / balance logic): explicit HH:MM from reason, else days × 8.
     */
    public function getOffsetHoursNeededAttribute(): float
    {
        if ($this->type !== 'offset') {
            return 0.0;
        }
        $mins = $this->parseOffsetHoursToDeductMinutes();
        if ($mins !== null) {
            return $mins / 60.0;
        }

        return (float) ($this->days * 8);
    }

    /**
     * Reason text without the teacher filing header line.
     */
    public function cleanTeacherExcusedReason(): string
    {
        $text = preg_replace('/^Teacher excused request by .+\n\n/s', '', (string) $this->reason, 1);

        return $text !== '' ? $text : (string) $this->reason;
    }

    /**
     * Absences that count toward student absence balance / merit (excludes teacher-filed excused requests).
     */
    public function scopeCountingTowardAbsenceMerits(Builder $query): Builder
    {
        return $query
            ->whereNull('teacher_excused_batch')
            ->where(function (Builder $q): void {
                $q->where('admin_officially_excused', false)
                    ->orWhereNull('admin_officially_excused');
            })
            ->whereDoesntHave('logs', function (Builder $logs): void {
                $logs->where('action', 'filed_by_teacher');
            });
    }

    /**
     * Whether this leave request was filed by a teacher on the student's behalf.
     */
    public function wasFiledByTeacher(): bool
    {
        if (filled($this->teacher_excused_batch)) {
            return true;
        }

        if ($this->relationLoaded('logs')
            && $this->logs->contains(fn ($log) => $log->action === 'filed_by_teacher')) {
            return true;
        }

        // Do not rely only on loaded logs (show page may keep a limited recent set).
        if ($this->logs()->where('action', 'filed_by_teacher')->exists()) {
            return true;
        }

        return str_starts_with(ltrim((string) $this->reason), 'Teacher excused request by ');
    }

    /**
     * Teacher who filed this excused request on behalf of the student.
     */
    public function filedByTeacher(): ?\App\Models\User
    {
        $this->loadMissing('logs.performer');

        $fromLoaded = $this->logs->firstWhere('action', 'filed_by_teacher')?->performer;
        if ($fromLoaded) {
            return $fromLoaded;
        }

        $log = $this->logs()
            ->where('action', 'filed_by_teacher')
            ->with('performer')
            ->oldest('id')
            ->first();

        return $log?->performer;
    }

    /**
     * All leave requests from the same teacher excused filing as this record.
     *
     * @return \Illuminate\Support\Collection<int, self>
     */
    public static function siblingsInTeacherExcusedFiling(self $request): \Illuminate\Support\Collection
    {
        $request->loadMissing(['logs.performer', 'user']);

        if ($request->type !== 'absent' && $request->type !== 'excused') {
            return collect([$request]);
        }

        if (! $request->wasFiledByTeacher()) {
            return collect([$request]);
        }

        if (filled($request->teacher_excused_batch)) {
            return static::query()
                ->where('teacher_excused_batch', $request->teacher_excused_batch)
                ->with(['user.university'])
                ->get()
                ->sortBy(fn (self $lr) => $lr->user?->name ?? '')
                ->values();
        }

        $groupKey = $request->teacherExcusedGroupKey();

        return static::query()
            ->whereIn('type', ['absent', 'excused'])
            ->whereHas('logs', function ($q): void {
                $q->where('action', 'filed_by_teacher');
            })
            ->with(['user.university', 'logs.performer'])
            ->get()
            ->filter(fn (self $lr) => $lr->teacherExcusedGroupKey() === $groupKey)
            ->sortBy(fn (self $lr) => $lr->user?->name ?? '')
            ->values();
    }

    /**
     * Group key for teacher excused filings (one form submission = one row in admin).
     */
    public function teacherExcusedGroupKey(): string
    {
        if (filled($this->teacher_excused_batch)) {
            return 'batch:'.$this->teacher_excused_batch;
        }

        $this->loadMissing(['logs.performer']);

        $teacher = $this->filedByTeacher();
        $filedLog = $this->logs->firstWhere('action', 'filed_by_teacher');
        $filedMinute = $filedLog?->created_at ?? $this->created_at;

        return implode('|', [
            'legacy',
            (string) ($teacher?->id ?? '0'),
            $this->start_date?->format('Y-m-d') ?? '',
            $this->end_date?->format('Y-m-d') ?? '',
            md5($this->cleanTeacherExcusedReason()),
            md5(json_encode($this->supporting_document_paths ?? [])),
            $filedMinute?->format('Y-m-d H:i') ?? '',
        ]);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, self>  $requests
     * @return array{
     *     group_key: string,
     *     filed_at: \Carbon\Carbon|null,
     *     teacher: ?\App\Models\User,
     *     start_date: ?\Carbon\Carbon,
     *     end_date: ?\Carbon\Carbon,
     *     reason: string,
     *     requests: \Illuminate\Support\Collection<int, self>,
     *     status_label: string,
     *     status_badge_class: string,
     * }
     */
    public static function summarizeTeacherExcusedFiling(\Illuminate\Support\Collection $requests): array
    {
        $sorted = $requests->sortBy('id')->values();
        $first = $sorted->first();
        $filedLog = $sorted
            ->flatMap(fn (self $r) => $r->logs)
            ->firstWhere('action', 'filed_by_teacher');

        $statuses = $sorted->pluck('status')->unique();
        if ($statuses->count() === 1) {
            $statusLabel = $sorted->first()->display_status;
            $statusBadge = $sorted->first()->status_badge_class;
        } elseif ($statuses->contains('pending')) {
            $statusLabel = 'Pending (partial)';
            $statusBadge = 'bg-yellow-100 text-yellow-800';
        } elseif ($statuses->contains('for_more_verification')) {
            $statusLabel = 'For verification (partial)';
            $statusBadge = 'bg-blue-100 text-blue-800';
        } else {
            $statusLabel = 'Mixed';
            $statusBadge = 'bg-gray-100 text-gray-800';
        }

        return [
            'group_key' => $first?->teacherExcusedGroupKey() ?? '',
            'filed_at' => $filedLog?->created_at ?? $first?->created_at,
            'teacher' => $first?->filedByTeacher(),
            'start_date' => $first?->start_date,
            'end_date' => $first?->end_date,
            'reason' => $first?->cleanTeacherExcusedReason() ?? '',
            'requests' => $sorted,
            'status_label' => $statusLabel,
            'status_badge_class' => $statusBadge,
        ];
    }
}
