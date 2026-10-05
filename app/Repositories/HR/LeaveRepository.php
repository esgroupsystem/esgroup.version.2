<?php

declare(strict_types=1);

namespace App\Repositories\HR;

use App\Enums\LeaveKind;
use App\Models\LeaveRecord;
use App\Repositories\Contracts\HR\LeaveRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class LeaveRepository implements LeaveRepositoryInterface
{
    private const CLOSED_FILTERS = ['inactive', 'completed', 'cancelled', 'terminated'];

    public function paginate(LeaveKind $kind, string $search, string $status, string $leaveType, string $garage, int $perPage = 10): LengthAwarePaginator
    {
        return $this->searched($kind, $search)
            ->when($status === 'active', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->whereNull('status')
                ->orWhere('status', '')
                ->orWhereRaw('LOWER(status) IN (?, ?)', ['active', 'on_leave'])))
            ->when(in_array($status, self::CLOSED_FILTERS, true), fn (Builder $query) => $query->whereRaw('LOWER(status) = ?', [$status]))
            ->when($leaveType !== '', fn (Builder $query) => $query->where('leave_type', $leaveType))
            ->when($garage !== '', fn (Builder $query) => $query->whereHas('employee', fn (Builder $employee) => $employee->where('garage', $garage)))
            ->orderByRaw("CASE WHEN status IS NULL OR status = '' THEN 1 WHEN LOWER(status) IN ('active', 'on_leave') THEN 1 WHEN LOWER(status) = 'inactive' THEN 2 WHEN LOWER(status) = 'completed' THEN 3 WHEN LOWER(status) = 'cancelled' THEN 4 WHEN LOWER(status) = 'terminated' THEN 5 ELSE 3 END ASC")
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function allMatching(LeaveKind $kind, string $search): Collection
    {
        return $this->searched($kind, $search)->get();
    }

    public function withEndDateInStatus(LeaveKind $kind, array $statuses): Collection
    {
        return $this->query($kind)
            ->with('employee')
            ->whereNotNull('end_date')
            ->whereIn('status', $statuses)
            ->get();
    }

    public function findOrFail(LeaveKind $kind, int $id, array $with = []): LeaveRecord
    {
        return $this->query($kind)->with($with)->findOrFail($id);
    }

    public function findForUpdate(LeaveKind $kind, int $id): LeaveRecord
    {
        return $this->query($kind)->with('employee')->lockForUpdate()->findOrFail($id);
    }

    public function create(LeaveKind $kind, array $attributes): LeaveRecord
    {
        return $this->query($kind)->create($attributes);
    }

    public function update(LeaveRecord $leave, array $attributes): void
    {
        $leave->update($attributes);
    }

    /** @return Builder<LeaveRecord> */
    private function query(LeaveKind $kind): Builder
    {
        return $kind->modelClass()::query();
    }

    /** @return Builder<LeaveRecord> */
    private function searched(LeaveKind $kind, string $search): Builder
    {
        return $this->query($kind)
            ->with(['employee.position'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $leave) use ($search): void {
                    $leave->whereHas('employee', fn (Builder $employee) => $employee
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%")
                        ->orWhere('employee_id_permanent', 'like', "%{$search}%")
                        ->orWhere('garage', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%"))
                        ->orWhere('leave_type', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('reason', 'like', "%{$search}%");
                });
            });
    }
}
