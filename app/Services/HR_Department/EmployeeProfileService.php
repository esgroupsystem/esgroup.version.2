<?php

declare(strict_types=1);

namespace App\Services\HR_Department;

use App\Models\Department;
use App\Models\Employee;
use App\Models\HrOffense;
use App\Models\Position;
use Carbon\Carbon;

final class EmployeeProfileService
{
    /** @return array<string, mixed> */
    public function data(Employee $employee): array
    {
        $employee->load([
            'asset',
            'histories' => fn ($query) => $query->with('offense')->orderByDesc('created_at'),
            'attachments', 'position', 'department', 'department.positions',
        ]);

        $offenses = HrOffense::query()->orderBy('section')->get();
        $departments = Department::query()->with('positions')->get();
        $deptMap = $departments->pluck('name', 'id');
        $posMap = Position::query()->pluck('title', 'id');
        $logs = $employee->logs()->with('user')->orderByDesc('created_at')->paginate(3)->withQueryString();

        $age = filled($employee->date_of_birth)
            ? Carbon::parse($employee->date_of_birth)->age.' yrs old'
            : '—';

        $tenure = $this->tenure($employee);
        $historyItems = $employee->histories->map(function ($history): array {
            $start = $history->start_date ? Carbon::parse($history->start_date) : null;
            $end = $history->end_date ? Carbon::parse($history->end_date) : null;
            $actions = $history->disciplinary_action;
            $actions = is_array($actions) ? $actions : (filled($actions) ? [(string) $actions] : []);
            $hasSda = in_array('Salary Deduction Authorization', $actions, true);
            $hasSuspension = in_array('Suspension', $actions, true);
            $sdaTotal = $history->sda_amount !== null ? (float) $history->sda_amount : null;
            $perCutoffAmount = $history->sda_terms ? (float) $history->sda_terms : null;
            $sdaStart = $history->sda_start_date ? Carbon::parse($history->sda_start_date) : null;
            $sdaEnd = $history->sda_end_date ? Carbon::parse($history->sda_end_date) : null;
            $susStart = $history->suspension_start_date ? Carbon::parse($history->suspension_start_date) : null;
            $susEnd = $history->suspension_end_date ? Carbon::parse($history->suspension_end_date) : null;

            return [
                'model' => $history,
                'title' => $history->title,
                'is_present' => $history->end_date === null,
                'range_text' => ($start?->format('M d, Y') ?? '—').' • '.($end?->format('M d, Y') ?? 'Present'),
                'duration_text' => $start ? ($end ? $start->diffForHumans($end, 1) : $start->diffForHumans(now(), 1)) : null,
                'offense_section' => ($history->title === 'Violations' && $history->offense) ? $history->offense->section : null,
                'actions' => $actions,
                'has_sda' => $hasSda,
                'has_suspension' => $hasSuspension,
                'sda_total' => $sdaTotal,
                'per_cutoff_amount' => $hasSda ? $perCutoffAmount : null,
                'months_duration' => ($hasSda && $sdaTotal !== null && $perCutoffAmount && $perCutoffAmount > 0)
                    ? (int) round(($sdaTotal / $perCutoffAmount) / 2)
                    : null,
                'sda_range_text' => $hasSda && ($sdaStart || $sdaEnd)
                    ? ($sdaStart?->format('M d, Y') ?? '—').' • '.($sdaEnd?->format('M d, Y') ?? 'Ongoing')
                    : null,
                'sus_range_text' => $hasSuspension && ($susStart || $susEnd)
                    ? ($susStart?->format('M d, Y') ?? '—').' • '.($susEnd?->format('M d, Y') ?? 'Ongoing')
                    : null,
            ];
        });

        $groupedIrHistories = $employee->histories
            ->where('title', 'Violations')
            ->groupBy(fn ($item) => $item->ir_number ?: 'NO-IR')
            ->map(function ($records, $irNumber): array {
                $first = $records->first();

                return [
                    'ir_number' => $irNumber,
                    'records' => $records,
                    'count' => $records->count(),
                    'actions' => is_array($first?->disciplinary_action) ? $first->disciplinary_action : [],
                    'first_record' => $first,
                ];
            });

        return compact('employee', 'departments', 'tenure', 'age', 'deptMap', 'posMap', 'logs', 'offenses', 'historyItems', 'groupedIrHistories');
    }

    private function tenure(Employee $employee): string
    {
        if (blank($employee->date_hired)) {
            return '—';
        }

        $hired = Carbon::parse($employee->date_hired)->startOfDay();
        $today = now()->startOfDay();
        if ($hired->gt($today)) {
            return 'Not started';
        }

        $diff = $hired->diff($today);
        $parts = [];
        if ($diff->y > 0) {
            $parts[] = $diff->y.' yr'.($diff->y > 1 ? 's' : '');
        }
        if ($diff->m > 0) {
            $parts[] = $diff->m.' mo'.($diff->m > 1 ? 's' : '');
        }
        if ($diff->y === 0 && $diff->m === 0) {
            $parts[] = $diff->d.' day'.($diff->d > 1 ? 's' : '');
        }

        return implode(' ', $parts);
    }
}
