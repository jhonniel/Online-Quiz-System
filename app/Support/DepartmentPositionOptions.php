<?php

namespace App\Support;

use App\Models\Department;
use Illuminate\Support\Collection;

final class DepartmentPositionOptions
{
    /**
     * @return Collection<int, list<array{id: int, name: string}>>
     */
    public static function mapForActiveDepartments(): Collection
    {
        return Department::query()
            ->active()
            ->with(['positions' => fn ($query) => $query->active()->orderBy('sort_order')->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Department $department) => [
                $department->id => $department->positions
                    ->map(fn ($position) => ['id' => $position->id, 'name' => $position->name])
                    ->values()
                    ->all(),
            ]);
    }
}
