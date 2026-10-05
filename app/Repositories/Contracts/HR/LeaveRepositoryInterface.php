<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\HR;

use App\Enums\LeaveKind;
use App\Models\LeaveRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/** The three leave tables, picked by LeaveKind. */
interface LeaveRepositoryInterface
{
    /**
     * The leave list: search (employee, leave type, status, reason) plus status, leave type
     * and garage filters. Open records first (active, inactive, completed, cancelled, terminated), then newest.
     *
     * @return LengthAwarePaginator<int, LeaveRecord>
     */
    public function paginate(LeaveKind $kind, string $search, string $status, string $leaveType, string $garage, int $perPage = 10): LengthAwarePaginator;

    /** @return Collection<int, LeaveRecord> every record matching the search, with employee (for the count cards) */
    public function allMatching(LeaveKind $kind, string $search): Collection;

    /**
     * Records past their end date that may need a reminder.
     *
     * @param  list<string>  $statuses
     * @return Collection<int, LeaveRecord>
     */
    public function withEndDateInStatus(LeaveKind $kind, array $statuses): Collection;

    /** @param list<string> $with */
    public function findOrFail(LeaveKind $kind, int $id, array $with = []): LeaveRecord;

    /** Locked row with its employee. */
    public function findForUpdate(LeaveKind $kind, int $id): LeaveRecord;

    /** @param array<string, mixed> $attributes */
    public function create(LeaveKind $kind, array $attributes): LeaveRecord;

    /** @param array<string, mixed> $attributes */
    public function update(LeaveRecord $leave, array $attributes): void;
}
