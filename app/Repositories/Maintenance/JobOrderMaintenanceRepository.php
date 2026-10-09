<?php

declare(strict_types=1);

namespace App\Repositories\Maintenance;

use App\Models\JobOrderMaintenance;
use App\Repositories\Contracts\Maintenance\JobOrderMaintenanceRepositoryInterface;
use Closure;
use DateTimeInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class JobOrderMaintenanceRepository implements JobOrderMaintenanceRepositoryInterface
{
    private const LIST_RELATIONS = ['bus', 'creator', 'statusPeriods'];

    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->filtered($filters, true)->with(self::LIST_RELATIONS)->latest()->paginate($perPage)->withQueryString();
    }

    public function countByStatus(array $filters): Collection
    {
        return $this->filtered($filters, false)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($total): int => (int) $total);
    }

    public function eachForExport(array $filters, Closure $callback): void
    {
        $this->filtered($filters, true)->with(self::LIST_RELATIONS)->latest()->chunk(500, $callback);
    }

    public function load(JobOrderMaintenance $jobOrder, array $relations): JobOrderMaintenance
    {
        return $jobOrder->load($relations);
    }

    public function findForUpdate(int $id): JobOrderMaintenance
    {
        return JobOrderMaintenance::query()->lockForUpdate()->findOrFail($id);
    }

    public function lastOdometerReading(int $busId): ?int
    {
        $reading = JobOrderMaintenance::query()->where('bus_id', $busId)->whereNotNull('odometer_reading')->latest('id')->value('odometer_reading');

        return $reading === null ? null : (int) $reading;
    }

    public function latestNumberLike(string $prefix): ?string
    {
        $number = JobOrderMaintenance::withTrashed()
            ->where('job_order_no', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('job_order_no');

        return is_string($number) ? $number : null;
    }

    public function numberExists(string $jobOrderNo): bool
    {
        return JobOrderMaintenance::withTrashed()->where('job_order_no', $jobOrderNo)->exists();
    }

    public function create(array $attributes): JobOrderMaintenance
    {
        return JobOrderMaintenance::query()->create($attributes);
    }

    public function update(JobOrderMaintenance $jobOrder, array $attributes): void
    {
        $jobOrder->forceFill($attributes)->save();
    }

    public function delete(JobOrderMaintenance $jobOrder): void
    {
        $jobOrder->delete();
    }

    public function endOpenPeriods(JobOrderMaintenance $jobOrder, DateTimeInterface $endedAt): void
    {
        $jobOrder->statusPeriods()->whereNull('ended_at')->lockForUpdate()->update(['ended_at' => $endedAt, 'updated_at' => $endedAt]);
    }

    public function startPeriod(JobOrderMaintenance $jobOrder, array $attributes): void
    {
        $jobOrder->statusPeriods()->create($attributes);
    }

    public function addHistory(JobOrderMaintenance $jobOrder, array $attributes): void
    {
        $jobOrder->histories()->create($attributes);
    }

    /**
     * @param  array{search: string, status: string, bus_id: ?int, date_filter: string, filter_date: string, filter_month: string, filter_year: string}  $filters
     * @return Builder<JobOrderMaintenance>
     */
    private function filtered(array $filters, bool $withStatus): Builder
    {
        $query = JobOrderMaintenance::query()
            ->search($filters['search'])
            ->when($withStatus && filled($filters['status']), fn (Builder $query) => $query->where('status', $filters['status']))
            ->when(filled($filters['bus_id']), fn (Builder $query) => $query->where('bus_id', $filters['bus_id']));

        if ($filters['date_filter'] === 'day' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['filter_date'])) {
            $query->whereDate('created_at', $filters['filter_date']);
        }
        if ($filters['date_filter'] === 'month' && preg_match('/^\d{4}-\d{2}$/', $filters['filter_month'])) {
            [$year, $month] = explode('-', $filters['filter_month']);
            $query->whereYear('created_at', $year)->whereMonth('created_at', $month);
        }
        if ($filters['date_filter'] === 'year' && preg_match('/^\d{4}$/', $filters['filter_year'])) {
            $query->whereYear('created_at', $filters['filter_year']);
        }

        return $query;
    }
}
