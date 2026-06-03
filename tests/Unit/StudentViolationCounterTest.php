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

    public function test_undertime_merit_from_filing_count_divides_by_five(): void
    {
        $this->assertSame(0, StudentViolationCounter::undertimeMeritFromFilingCount(0));
        $this->assertSame(0, StudentViolationCounter::undertimeMeritFromFilingCount(4));
        $this->assertSame(1, StudentViolationCounter::undertimeMeritFromFilingCount(5));
        $this->assertSame(1, StudentViolationCounter::undertimeMeritFromFilingCount(8));
        $this->assertSame(2, StudentViolationCounter::undertimeMeritFromFilingCount(10));
    }

    public function test_excess_absence_merits_is_approved_minus_allowable(): void
    {
        $this->assertSame(0, StudentViolationCounter::excessAbsenceMerits(3, 3.0));
        $this->assertSame(1, StudentViolationCounter::excessAbsenceMerits(4, 3.0));
        $this->assertSame(0, StudentViolationCounter::excessAbsenceMerits(4, 3.5));
        $this->assertSame(2, StudentViolationCounter::excessAbsenceMerits(6, 3.5));
    }
}
