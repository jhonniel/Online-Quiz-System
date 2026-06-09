<?php

namespace App\Support;

use App\Models\EmployeePayslip;
use Illuminate\Support\Collection;

final class PayslipGrouper
{
    /**
     * @param  iterable<int, EmployeePayslip>  $payslips
     * @return list<array{
     *     key: string,
     *     label: string,
     *     count: int,
     *     months: list<array{
     *         key: string,
     *         label: string,
     *         count: int,
     *         cutoffs: list<array{
     *             key: string,
     *             label: string,
     *             period_start: string,
     *             period_end: string,
     *             count: int,
     *             payslips: Collection<int, EmployeePayslip>
     *         }>
     *     }>
     * }>
     */
    public static function group(iterable $payslips): array
    {
        /** @var array<string, array<string, array<string, list<EmployeePayslip>>>> $tree */
        $tree = [];

        foreach ($payslips as $payslip) {
            if (! $payslip instanceof EmployeePayslip) {
                continue;
            }

            $yearKey = $payslip->period_end->format('Y');
            $monthKey = $payslip->period_end->format('Y-m');
            $cutoffKey = $payslip->period_start->format('Y-m-d').'|'.$payslip->period_end->format('Y-m-d');

            $tree[$yearKey][$monthKey][$cutoffKey][] = $payslip;
        }

        krsort($tree, SORT_STRING);

        $grouped = [];

        foreach ($tree as $yearKey => $months) {
            $yearCount = 0;
            $monthGroups = [];

            krsort($months, SORT_STRING);

            foreach ($months as $monthKey => $cutoffs) {
                $monthCount = 0;
                $cutoffGroups = [];

                uksort($cutoffs, function (string $a, string $b) {
                    return strcmp($b, $a);
                });

                foreach ($cutoffs as $cutoffKey => $items) {
                    usort($items, fn (EmployeePayslip $a, EmployeePayslip $b) => strcmp($a->employee_name, $b->employee_name));

                    /** @var EmployeePayslip $first */
                    $first = $items[0];
                    $count = count($items);
                    $monthCount += $count;

                    $cutoffGroups[] = [
                        'key' => $cutoffKey,
                        'label' => $first->periodLabel(),
                        'period_start' => $first->period_start->format('Y-m-d'),
                        'period_end' => $first->period_end->format('Y-m-d'),
                        'count' => $count,
                        'payslips' => collect($items),
                    ];
                }

                $yearCount += $monthCount;

                /** @var EmployeePayslip $sample */
                $sample = $cutoffGroups[0]['payslips']->first();

                $monthGroups[] = [
                    'key' => $monthKey,
                    'label' => $sample->period_end->format('F Y'),
                    'count' => $monthCount,
                    'cutoffs' => $cutoffGroups,
                ];
            }

            $grouped[] = [
                'key' => $yearKey,
                'label' => $yearKey,
                'count' => $yearCount,
                'months' => $monthGroups,
            ];
        }

        return $grouped;
    }
}
