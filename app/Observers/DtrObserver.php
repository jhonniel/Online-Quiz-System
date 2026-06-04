<?php

namespace App\Observers;

use App\Models\Dtr;
use App\Services\StudentOjtPostCompletionService;

class DtrObserver
{
    public function __construct(
        private StudentOjtPostCompletionService $studentOjtPostCompletionService
    ) {}

    public function saved(Dtr $dtr): void
    {
        if (! $dtr->user_id) {
            return;
        }

        $this->studentOjtPostCompletionService->syncForStudentId((int) $dtr->user_id, false);
    }
}
