<?php

namespace App\Support;

use App\Models\Starlink;
use Illuminate\Support\Collection;

class StarlinkReplacementGroup
{
    public static function leaderId(Starlink $starlink): ?int
    {
        return $starlink->replacement_group_id ? (int) $starlink->replacement_group_id : null;
    }

    public static function isGrouped(Starlink $starlink): bool
    {
        return $starlink->replacement_group_id !== null;
    }

    /**
     * @return Collection<int, Starlink>
     */
    public static function membersForLeader(?int $leaderId, ?int $excludeId = null): Collection
    {
        if (! $leaderId) {
            return collect();
        }

        $query = Starlink::query()
            ->where(function ($q) use ($leaderId) {
                $q->where('replacement_group_id', $leaderId)
                    ->orWhere('id', $leaderId);
            })
            ->orderByDesc('created_at');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->get();
    }

    /**
     * @return Collection<int, Starlink>
     */
    public static function members(Starlink $starlink, bool $includeSelf = true): Collection
    {
        $leaderId = self::leaderId($starlink);
        if (! $leaderId) {
            return $includeSelf ? collect([$starlink]) : collect();
        }

        return self::membersForLeader($leaderId, $includeSelf ? null : $starlink->id);
    }

    public static function memberCount(Starlink $starlink): int
    {
        $leaderId = self::leaderId($starlink);

        return $leaderId
            ? self::membersForLeader($leaderId)->count()
            : 1;
    }

    /**
     * @param  array<int, int|string>  $linkedIds
     */
    public static function syncLinks(Starlink $starlink, array $linkedIds): void
    {
        $linkedIds = collect($linkedIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0 && $id !== (int) $starlink->id)
            ->unique()
            ->values()
            ->all();

        self::detach($starlink);

        if ($linkedIds === []) {
            return;
        }

        $starlink->refresh();

        foreach ($linkedIds as $linkedId) {
            $other = Starlink::find($linkedId);
            if ($other) {
                self::merge($starlink, $other);
                $starlink->refresh();
            }
        }
    }

    public static function detach(Starlink $starlink): void
    {
        $leaderId = self::leaderId($starlink);
        if (! $leaderId) {
            return;
        }

        $starlink->update(['replacement_group_id' => null]);

        $remaining = self::membersForLeader($leaderId);

        if ($remaining->count() <= 1) {
            foreach ($remaining as $member) {
                $member->update(['replacement_group_id' => null]);
            }

            return;
        }

        if ((int) $starlink->id === $leaderId) {
            $newLeaderId = (int) $remaining->min('id');
            self::assignGroup($remaining->pluck('id')->all(), $newLeaderId);
        }
    }

    public static function merge(Starlink $a, Starlink $b): void
    {
        if ((int) $a->id === (int) $b->id) {
            return;
        }

        $ids = collect([$a->id, $b->id]);

        foreach ([$a, $b] as $device) {
            $leaderId = self::leaderId($device);
            if ($leaderId) {
                $ids = $ids->merge(self::membersForLeader($leaderId)->pluck('id'));
            }
        }

        $newLeaderId = (int) $ids->unique()->min();
        self::assignGroup($ids->unique()->all(), $newLeaderId);
    }

    /**
     * @param  array<int, int>  $starlinkIds
     */
    private static function assignGroup(array $starlinkIds, int $leaderId): void
    {
        if ($starlinkIds === []) {
            return;
        }

        Starlink::whereIn('id', $starlinkIds)->update(['replacement_group_id' => $leaderId]);
    }

    /**
     * @param  Collection<int, Starlink>  $starlinks
     * @return array<int, array{count: int, members: Collection<int, Starlink>}>
     */
    public static function summariesFor(Collection $starlinks): array
    {
        $summaries = [];

        foreach ($starlinks as $starlink) {
            $leaderId = self::leaderId($starlink);
            if (! $leaderId || isset($summaries[$leaderId])) {
                continue;
            }

            $members = self::membersForLeader($leaderId);
            $summaries[$leaderId] = [
                'count' => $members->count(),
                'members' => $members,
            ];
        }

        return $summaries;
    }
}
