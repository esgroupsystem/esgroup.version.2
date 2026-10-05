<?php

declare(strict_types=1);

namespace App\Repositories\IT;

use App\Models\JobOrder;
use App\Models\JobOrderFile;
use App\Models\JobOrderLog;
use App\Models\JobOrderNote;
use App\Repositories\Contracts\IT\JobOrderRepositoryInterface;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class JobOrderRepository implements JobOrderRepositoryInterface
{
    public function paginateByStatus(array $statuses, string $search, int $perPage = 10): LengthAwarePaginator
    {
        return JobOrder::query()
            ->with('bus')
            ->whereIn('job_status', $statuses)
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('job_creator', 'like', "%{$search}%")
                        ->orWhere('job_type', 'like', "%{$search}%")
                        ->orWhere('job_status', 'like', "%{$search}%")
                        ->orWhere('driver_name', 'like', "%{$search}%")
                        ->orWhere('conductor_name', 'like', "%{$search}%")
                        ->orWhereHas('bus', function (Builder $bus) use ($search): void {
                            $bus->where('body_number', 'like', "%{$search}%")
                                ->orWhere('plate_number', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('job_date_filled')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function countByStatus(array $statuses): int
    {
        return JobOrder::query()->whereIn('job_status', $statuses)->count();
    }

    public function countCreatedOn(CarbonInterface $day): int
    {
        return JobOrder::query()->whereDate('created_at', $day)->count();
    }

    public function countByType(): Collection
    {
        return JobOrder::query()
            ->select('job_type')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('job_type')
            ->pluck('total', 'job_type')
            ->map(fn ($total): int => (int) $total);
    }

    public function createdSince(CarbonInterface $from): Collection
    {
        return JobOrder::query()
            ->where('created_at', '>=', $from)
            ->get(['job_status', 'created_at']);
    }

    public function latestByStatus(array $statuses, int $limit): Collection
    {
        return JobOrder::query()
            ->with('bus')
            ->whereIn('job_status', $statuses)
            ->orderByDesc('job_date_filled')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function latestWithBus(int $limit): Collection
    {
        return JobOrder::query()
            ->with('bus')
            ->orderByDesc('job_date_filled')
            ->limit($limit)
            ->get();
    }

    public function exportRows(): Collection
    {
        return JobOrder::query()->get(['id', 'job_creator', 'job_type', 'job_status', 'job_date_filled']);
    }

    public function findOrFail(int $id, array $with = []): JobOrder
    {
        return JobOrder::query()->with($with)->findOrFail($id);
    }

    public function findForUpdate(int $id, array $with = []): JobOrder
    {
        return JobOrder::query()->with($with)->lockForUpdate()->findOrFail($id);
    }

    public function create(array $attributes): JobOrder
    {
        return JobOrder::query()->create($attributes);
    }

    public function update(JobOrder $job, array $attributes): void
    {
        $job->update($attributes);
    }

    public function delete(JobOrder $job): void
    {
        JobOrderFile::query()->where('job_id', $job->id)->delete();
        JobOrderNote::query()->where('joborder_id', $job->id)->delete();
        JobOrderLog::query()->where('joborder_id', $job->id)->delete();
        $job->delete();
    }

    public function addFile(JobOrder $job, array $attributes): JobOrderFile
    {
        return JobOrderFile::query()->create(['job_id' => $job->id] + $attributes);
    }

    public function findFileOrFail(JobOrder $job, int $fileId): JobOrderFile
    {
        return $job->files()->findOrFail($fileId);
    }

    public function addNote(JobOrder $job, array $attributes): JobOrderNote
    {
        return JobOrderNote::query()->create(['joborder_id' => $job->id] + $attributes);
    }

    public function addLog(JobOrder $job, int $userId, string $action, array $meta): JobOrderLog
    {
        return JobOrderLog::query()->create([
            'joborder_id' => $job->id,
            'user_id' => $userId,
            'action' => $action,
            'meta' => $meta,
        ]);
    }

    public function recordView(JobOrder $job, int $userId): void
    {
        JobOrderLog::query()->updateOrCreate(
            ['joborder_id' => $job->id, 'user_id' => $userId, 'action' => 'viewed'],
            ['meta' => ['message' => 'User viewed the job order details']],
        );
    }

    public function logs(JobOrder $job): Collection
    {
        return JobOrderLog::query()
            ->with('user')
            ->where('joborder_id', $job->id)
            ->orderByDesc('created_at')
            ->get();
    }
}
