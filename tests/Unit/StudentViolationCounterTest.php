<?php

namespace Tests\Unit;

use App\Support\StudentViolationCounter;
use PHPUnit\Framework\TestCase;

class StudentViolationCounterTest extends TestCase
{
    public function test_compose_breakdown_sums_all_violation_types(): void
    {
        $breakdown = StudentViolationCounter::composeBreakdown(2, 1, 1);

        $this->assertSame(2, $breakdown['undertime']);
        $this->assertSame(1, $breakdown['excess_absence']);
        $this->assertSame(1, $breakdown['manual']);
        $this->assertSame(4, $breakdown['total']);
    }

    public function test_excess_absence_merits_respects_allowable_balance(): void
    {
        $this->assertSame(0, StudentViolationCounter::excessAbsenceMerits(3, 3.0));
        $this->assertSame(1, StudentViolationCounter::excessAbsenceMerits(4, 3.0));
        $this->assertSame(1, StudentViolationCounter::excessAbsenceMerits(4, 3.5));
        $this->assertSame(3, StudentViolationCounter::excessAbsenceMerits(6, 3.5));
    }
}
