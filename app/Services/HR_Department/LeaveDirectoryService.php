<?php

declare(strict_types=1);

namespace App\Services\HR_Department;

use App\Enums\HrPositionType;
use App\Models\ConductorLeave;
use App\Models\DriverLeave;
use App\Models\Employee;
use App\Models\EmployeeLeave;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class LeaveDirectoryService
{
    /** @return array{leaves:LengthAwarePaginator,today:Carbon,counts:array<string,int>,garageSummary:Collection} */
    public function driverIndex(Request $request): array
    {
        return $this->buildIndex(DriverLeave::class, $request);
    }

    /** @return array{leaves:LengthAwarePaginator,today:Carbon,counts:array<string,int>,garageSummary:Collection} */
    public function conductorIndex(Request $request): array
    {
        return $this->buildIndex(ConductorLeave::class, $request);
    }

    /** @return array{leaves:LengthAwarePaginator,today:Carbon,counts:array<string,int>,garageSummary:Collection} */
    public function employeeIndex(Request $request): array
    {
        return $this->buildIndex(EmployeeLeave::class, $request);
    }

    /** @return EloquentCollection<int, Employee> */
    public function drivers(?DriverLeave $currentLeave = null): EloquentCollection
    {
        return $this->eligibleEmployees(HrPositionType::Driver->value, $currentLeave?->employee_id);
    }

    /** @return EloquentCollection<int, Employee> */
    public function conductors(?ConductorLeave $currentLeave = null): EloquentCollection
    {
        return $this->eligibleEmployees(HrPositionType::Conductor->value, $currentLeave?->employee_id);
    }

    /** @return EloquentCollection<int, Employee> */
    public function employees(?EmployeeLeave $currentLeave = null): EloquentCollection
    {
        return $this->eligibleEmployees(null, $currentLeave?->employee_id);
    }

    /**
     * @param  class-string<DriverLeave|ConductorLeave|EmployeeLeave>  $leaveClass
     * @return array{leaves:LengthAwarePaginator,today:Carbon,counts:array<string,int>,garageSummary:Collection}
     */
    private function buildIndex(string $leaveClass, Request $request): array
    {
        $today = Carbon::now('Asia/Manila')->startOfDay();
        $search = trim((string) $request->input('search', ''));
        $status = strtolower(trim((string) $request->input('status', '')));
        $leaveType = trim((string) $request->input('leave_type', ''));
        $garage = trim((string) $request->input('garage', ''));

        $baseQuery = $leaveClass::query()
            ->with(['employee.position'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $leaveQuery) use ($search): void {
                    $leaveQuery
                        ->whereHas('employee', function (Builder $employeeQuery) use ($search): void {
                            $employeeQuery
                                ->where('full_name', 'like', "%{$search}%")
                                ->orWhere('employee_id', 'like', "%{$search}%")
                                ->orWhere('employee_id_permanent', 'like', "%{$search}%")
                                ->orWhere('garage', 'like', "%{$search}%")
                                ->orWhere('company', 'like', "%{$search}%");
                        })
                        ->orWhere('leave_type', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('reason', 'like', "%{$search}%");
                });
            });

        $leaves = (clone $baseQuery)
            ->when($status === 'active', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->whereNull('status')
                ->orWhere('status', '')
                ->orWhereRaw('LOWER(status) IN (?, ?)', ['active', 'on_leave'])))
            ->when(in_array($status, ['inactive', 'completed', 'cancelled', 'terminated'], true), fn (Builder $query) => $query->whereRaw('LOWER(status) = ?', [$status]))
            ->when($leaveType !== '', fn (Builder $query) => $query->where('leave_type', $leaveType))
            ->when($garage !== '', fn (Builder $query) => $query->whereHas('employee', fn (Builder $employeeQuery) => $employeeQuery->where('garage', $garage)))
            ->orderByRaw("CASE WHEN status IS NULL OR status = '' THEN 1 WHEN LOWER(status) IN ('active', 'on_leave') THEN 1 WHEN LOWER(status) = 'inactive' THEN 2 WHEN LOWER(status) = 'completed' THEN 3 WHEN LOWER(status) = 'cancelled' THEN 4 WHEN LOWER(status) = 'terminated' THEN 5 ELSE 3 END ASC")
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $this->decorateLeaveRows($leaves, $today);
        $allForCounts = (clone $baseQuery)->get();

        $counts = [
            'active' => 0,
            'first' => 0,
            'second' => 0,
            'inactive' => 0,
            'termination' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'total' => $allForCounts->count(),
        ];

        foreach ($allForCounts as $leave) {
            $level = (int) ($leave->offense_level ?? 0);
            $status = strtolower((string) ($leave->status ?? ''));

            if (in_array($status, ['inactive', 'completed', 'cancelled'], true)) {
                $counts[$status]++;
            }
            if ($level === 1) {
                $counts['first']++;
            } elseif ($level === 2) {
                $counts['second']++;
            } elseif ($level >= 3 || $status === 'terminated') {
                $counts['termination']++;
            } elseif (! in_array($status, ['cancelled', 'terminated', 'completed', 'inactive'], true)) {
                $counts['active']++;
            }
        }

        $garageSummary = $allForCounts
            ->groupBy(fn ($leave) => $leave->employee?->garage ?: 'No Garage Assigned')
            ->map(fn ($items, $garage): array => [
                'garage' => $garage,
                'total' => $items->count(),
                'active' => $items->filter(fn ($leave): bool => in_array(strtolower((string) ($leave->status ?? '')), ['', 'active', 'on_leave'], true))->count(),
                'first_notice' => $items->where('offense_level', 1)->count(),
                'second_notice' => $items->where('offense_level', 2)->count(),
                'inactive' => $items->filter(fn ($leave): bool => strtolower((string) ($leave->status ?? '')) === 'inactive')->count(),
                'terminated' => $items->filter(fn ($leave): bool => strtolower((string) ($leave->status ?? '')) === 'terminated')->count(),
            ])
            ->sortBy('garage')
            ->values();

        return compact('leaves', 'today', 'counts', 'garageSummary');
    }

    /** @return EloquentCollection<int, Employee> */
    private function eligibleEmployees(?string $positionTitle, int|string|null $currentEmployeeId = null): EloquentCollection
    {
        return Employee::query()
            ->with('position')
            ->when($positionTitle !== null, fn (Builder $query) => $query->whereHas('position', fn (Builder $positionQuery) => $positionQuery->where('title', $positionTitle)))
            ->when($positionTitle === null, fn (Builder $query) => $query->where(fn (Builder $employeeQuery) => $employeeQuery->whereDoesntHave('position')->orWhereHas('position', fn (Builder $positionQuery) => $positionQuery->whereNotIn('title', [HrPositionType::Driver->value, HrPositionType::Conductor->value]))))
            ->where(function (Builder $query) use ($currentEmployeeId): void {
                $query->whereIn('status', ['Active', 'Active(Re-Entry)']);
                if ($currentEmployeeId !== null) {
                    $query->orWhere('id', $currentEmployeeId);
                }
            })
            ->orderBy('garage')
            ->orderBy('full_name')
            ->get();
    }

    private function decorateLeaveRows(LengthAwarePaginator $leaves, Carbon $today): void
    {
        foreach ($leaves as $leave) {
            $rawStatus = strtolower((string) ($leave->status ?? ''));
            $statusLabel = $leave->status ? ucfirst((string) $leave->status) : 'Active';
            $statusColor = match ($rawStatus) {
                'completed' => 'success',
                'cancelled' => 'secondary',
                'terminated' => 'danger',
                'inactive' => 'warning',
                default => 'primary',
            };
            $leave->record_status_badge = '<span class="badge rounded-pill badge-subtle-'.$statusColor.' text-'.$statusColor.'">'.$statusLabel.'</span>';

            $level = (int) ($leave->offense_level ?? 0);
            $leave->level_label = match (true) {
                $level >= 3 => 'Final Notice',
                $level === 2 => '2nd Notice',
                $level === 1 => '1st Notice',
                default => 'No Notice',
            };
            $leave->status_label = $statusLabel;
            $leave->record_status_tone = $statusColor;
            $leave->remaining = ['label' => $statusLabel, 'tone' => $statusColor];

            if (in_array($rawStatus, ['cancelled', 'completed', 'terminated'], true)) {
                $leave->remaining_status = $leave->record_status_badge;

                continue;
            }

            $end = $leave->end_date ? Carbon::parse($leave->end_date)->startOfDay() : null;
            if (! $end) {
                $leave->remaining_status = $leave->record_status_badge;

                continue;
            }

            if ($today->lte($end)) {
                $remainingDays = (int) $today->diffInDays($end) + 1;
                $leave->remaining_status = '<span class="badge rounded-pill badge-subtle-success text-success">On Leave: '.$remainingDays.' '.($remainingDays === 1 ? 'day' : 'days').' left</span>';
                $leave->remaining = ['label' => 'On Leave: '.$remainingDays.' '.($remainingDays === 1 ? 'day' : 'days').' left', 'tone' => 'success'];

                continue;
            }

            $daysAfterEnd = (int) $end->diffInDays($today);
            $leave->remaining = match (true) {
                $daysAfterEnd === 1 => ['label' => 'Ready for Duty', 'tone' => 'primary'],
                $daysAfterEnd <= 9 => ['label' => 'Warning for 1st Notice', 'tone' => 'info'],
                $daysAfterEnd <= 22 => ['label' => 'Warning for 2nd Notice', 'tone' => 'warning'],
                default => ['label' => 'Subject for Final Notice', 'tone' => 'danger'],
            };
            $leave->remaining_status = match (true) {
                $daysAfterEnd === 1 => '<span class="badge rounded-pill badge-subtle-primary text-primary">Ready for Duty</span>',
                $daysAfterEnd <= 9 => '<span class="badge rounded-pill badge-subtle-info text-info">Warning for 1st Notice</span>',
                $daysAfterEnd <= 22 => '<span class="badge rounded-pill badge-subtle-warning text-warning">Warning for 2nd Notice</span>',
                default => '<span class="badge rounded-pill badge-subtle-danger text-danger">Subject for Final Notice</span>',
            };
        }
    }
}
