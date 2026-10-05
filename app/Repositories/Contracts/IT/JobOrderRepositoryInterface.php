<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\IT;

use App\Models\JobOrder;
use App\Models\JobOrderFile;
use App\Models\JobOrderLog;
use App\Models\JobOrderNote;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface JobOrderRepositoryInterface
{
    /**
     * Newest filed first, with the bus.
     *
     * @param  list<string>  $statuses
     * @return LengthAwarePaginator<int, JobOrder>
     */
    public function paginateByStatus(array $statuses, string $search, int $perPage = 10): LengthAwarePaginator;

    /** @param list<string> $statuses */
    public function countByStatus(array $statuses): int;

    public function countCreatedOn(CarbonInterface $day): int;

    /** @return Collection<string, int> job_type => total */
    public function countByType(): Collection;

    /** @return Collection<int, JobOrder> only job_status and created_at */
    public function createdSince(CarbonInterface $from): Collection;

    /**
     * @param  list<string>  $statuses
     * @return Collection<int, JobOrder>
     */
    public function latestByStatus(array $statuses, int $limit): Collection;

    /** @return Collection<int, JobOrder> */
    public function latestWithBus(int $limit): Collection;

    /** @return Collection<int, JobOrder> */
    public function exportRows(): Collection;

    /** @param list<string> $with */
    public function findOrFail(int $id, array $with = []): JobOrder;

    /** @param list<string> $with */
    public function findForUpdate(int $id, array $with = []): JobOrder;

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): JobOrder;

    /** @param array<string, mixed> $attributes */
    public function update(JobOrder $job, array $attributes): void;

    /** Deletes the job order with its file, note and log rows (not the stored files). */
    public function delete(JobOrder $job): void;

    /** @param array<string, mixed> $attributes */
    public function addFile(JobOrder $job, array $attributes): JobOrderFile;

    public function findFileOrFail(JobOrder $job, int $fileId): JobOrderFile;

    /** @param array<string, mixed> $attributes */
    public function addNote(JobOrder $job, array $attributes): JobOrderNote;

    /** @param array<string, mixed> $meta */
    public function addLog(JobOrder $job, int $userId, string $action, array $meta): JobOrderLog;

    /** Keeps one "viewed" row per user, refreshed on every view. */
    public function recordView(JobOrder $job, int $userId): void;

    /** @return Collection<int, JobOrderLog> newest first */
    public function logs(JobOrder $job): Collection;
}
