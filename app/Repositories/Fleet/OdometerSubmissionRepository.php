<?php

declare(strict_types=1);

namespace App\Repositories\Fleet;

use App\Models\OdometerSubmission;
use App\Repositories\Contracts\Fleet\OdometerSubmissionRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class OdometerSubmissionRepository implements OdometerSubmissionRepositoryInterface
{
    public function forPeriod(?int $busId, string $from, string $to): Collection
    {
        return OdometerSubmission::query()
            ->leftJoin('bus_details', 'odometer_submissions.bus_detail_id', '=', 'bus_details.id')
            ->select(
                'odometer_submissions.id',
                'odometer_submissions.bus_detail_id',
                'odometer_submissions.date',
                'odometer_submissions.time',
                'odometer_submissions.driver_name',
                'odometer_submissions.new_odometer',
                'odometer_submissions.diesel_consumption',
                'odometer_submissions.date_bus_deployed',
                'bus_details.garage',
                'bus_details.name as bus_name',
                'bus_details.body_number',
                'bus_details.plate_number'
            )
            ->when($busId !== null, fn (Builder $query) => $query->where('odometer_submissions.bus_detail_id', $busId))
            ->whereBetween('odometer_submissions.date', [$from, $to])
            ->orderBy('odometer_submissions.bus_detail_id')
            ->orderBy('odometer_submissions.date')
            ->orderBy('odometer_submissions.time')
            ->orderBy('odometer_submissions.id')
            ->get();
    }

    public function lastReadingBefore(int $busId, string $date): ?int
    {
        $reading = OdometerSubmission::query()
            ->where('bus_detail_id', $busId)
            ->whereDate('date', '<', $date)
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->orderByDesc('id')
            ->value('new_odometer');

        return $reading === null ? null : (int) $reading;
    }

    public function latestReading(int $busId): ?int
    {
        $reading = OdometerSubmission::query()->where('bus_detail_id', $busId)->latest()->value('new_odometer');

        return $reading === null ? null : (int) $reading;
    }

    public function existsAt(int $busId, string $date, string $time): bool
    {
        return OdometerSubmission::query()
            ->where('bus_detail_id', $busId)
            ->whereDate('date', $date)
            ->whereTime('time', $time)
            ->lockForUpdate()
            ->exists();
    }

    public function previous(int $busId, string $date, string $time, ?int $exceptId = null): ?OdometerSubmission
    {
        return $this->neighbour($busId, $exceptId)
            ->where(fn (Builder $query) => $query
                ->whereDate('date', '<', $date)
                ->orWhere(fn (Builder $same) => $same->whereDate('date', $date)->whereTime('time', '<', $time)))
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->orderByDesc('id')
            ->first();
    }

    public function next(int $busId, string $date, string $time, ?int $exceptId = null): ?OdometerSubmission
    {
        return $this->neighbour($busId, $exceptId)
            ->where(fn (Builder $query) => $query
                ->whereDate('date', '>', $date)
                ->orWhere(fn (Builder $same) => $same->whereDate('date', $date)->whereTime('time', '>', $time)))
            ->orderBy('date')
            ->orderBy('time')
            ->orderBy('id')
            ->first();
    }

    public function create(array $attributes): OdometerSubmission
    {
        return OdometerSubmission::query()->create($attributes);
    }

    public function update(OdometerSubmission $submission, array $attributes): void
    {
        $submission->update($attributes);
    }

    public function delete(OdometerSubmission $submission): void
    {
        $submission->delete();
    }

    /** @return Builder<OdometerSubmission> */
    private function neighbour(int $busId, ?int $exceptId): Builder
    {
        return OdometerSubmission::query()
            ->where('bus_detail_id', $busId)
            ->when($exceptId !== null, fn (Builder $query) => $query->whereKeyNot($exceptId))
            ->lockForUpdate();
    }
}
