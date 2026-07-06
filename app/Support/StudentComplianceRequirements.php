<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\StudentNda;
use App\Models\User;
use App\Models\UserActivity;

final class StudentComplianceRequirements
{
    /**
     * @return list<array{
     *     key: string,
     *     label: string,
     *     description: string,
     *     complete: bool,
     *     status_label: string,
     *     status_tone: string,
     *     url: string
     * }>
     */
    public static function itemsFor(User $user): array
    {
        if (! $user->isStudent()) {
            return [];
        }

        $user->loadMissing('studentNda');

        $items = [
            self::ndaItem($user),
        ];

        if (self::torRequired()) {
            $items[] = self::torItem($user);
        }

        return $items;
    }

    /**
     * @return list<array{
     *     key: string,
     *     label: string,
     *     description: string,
     *     complete: bool,
     *     status_label: string,
     *     status_tone: string,
     *     url: string
     * }>
     */
    public static function incompleteItemsFor(User $user): array
    {
        return array_values(array_filter(
            self::itemsFor($user),
            static fn (array $item): bool => ! ($item['complete'] ?? false)
        ));
    }

    public static function torRequired(): bool
    {
        return trim((string) Setting::get('hiring_tor_pdf', '')) !== '';
    }

    public static function torComplete(User $user): bool
    {
        if (session('student_tor_acknowledged') === true) {
            return true;
        }

        return UserActivity::query()
            ->where('user_id', $user->id)
            ->where('action', 'student_tor_reviewed')
            ->exists();
    }

    public static function ndaComplete(User $user): bool
    {
        $user->loadMissing('studentNda');

        return $user->studentNda?->isAdminApproved() ?? false;
    }

    /**
     * @return array{
     *     key: string,
     *     label: string,
     *     description: string,
     *     complete: bool,
     *     status_label: string,
     *     status_tone: string,
     *     url: string
     * }
     */
    private static function ndaItem(User $user): array
    {
        $nda = $user->studentNda;

        if (self::ndaComplete($user)) {
            return [
                'key' => 'nda',
                'label' => 'Non-Disclosure Agreement (NDA)',
                'description' => 'Upload your signed NDA and wait for administrator approval to record attendance.',
                'complete' => true,
                'status_label' => 'Approved',
                'status_tone' => 'emerald',
                'url' => route('user.nda.index'),
            ];
        }

        if (! $nda || ! $nda->hasSignedUpload()) {
            $rejected = $nda?->isRejected() ?? false;

            return [
                'key' => 'nda',
                'label' => 'Non-Disclosure Agreement (NDA)',
                'description' => $rejected
                    ? 'Your previous NDA was rejected. Upload a corrected signed PDF.'
                    : 'Generate, sign, and upload your NDA for admin review.',
                'complete' => false,
                'status_label' => $rejected ? 'Rejected — reupload required' : 'Not uploaded',
                'status_tone' => $rejected ? 'rose' : 'amber',
                'url' => route('user.nda.index'),
            ];
        }

        if ($nda->isPendingApproval()) {
            return [
                'key' => 'nda',
                'label' => 'Non-Disclosure Agreement (NDA)',
                'description' => 'Your signed NDA is waiting for administrator approval.',
                'complete' => false,
                'status_label' => 'Pending admin review',
                'status_tone' => 'amber',
                'url' => route('user.nda.index'),
            ];
        }

        return [
            'key' => 'nda',
            'label' => 'Non-Disclosure Agreement (NDA)',
            'description' => 'Complete your NDA submission to unlock attendance recording.',
            'complete' => false,
            'status_label' => $nda->approvalStatusLabel(),
            'status_tone' => 'amber',
            'url' => route('user.nda.index'),
        ];
    }

    /**
     * @return array{
     *     key: string,
     *     label: string,
     *     description: string,
     *     complete: bool,
     *     status_label: string,
     *     status_tone: string,
     *     url: string
     * }
     */
    private static function torItem(User $user): array
    {
        $complete = self::torComplete($user);

        return [
            'key' => 'tor',
            'label' => 'Term of Reference (TOR)',
            'description' => 'Read the Term of Reference document for your internship program.',
            'complete' => $complete,
            'status_label' => $complete ? 'Reviewed' : 'Not reviewed',
            'status_tone' => $complete ? 'emerald' : 'amber',
            'url' => route('user.tor'),
        ];
    }
}
