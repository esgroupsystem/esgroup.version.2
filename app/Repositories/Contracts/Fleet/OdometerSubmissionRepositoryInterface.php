<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Fleet;

use App\Models\OdometerSubmission;
use Illuminate\Support\Collection;

/** Odometer readings (`odometer_submissions`), from the mobile app and manual encoding. */
interface OdometerSubmissionRepositoryInterface
{
    /**
     * Readings dated $from..$to with the bus columns (garage, bus_name, body_number, plate_number),
     * ordered by bus, date, time, id.
     *
     * @return Collection<int, OdometerSubmission>
     */
    public function forPeriod(?int $busId, string $from, string $to): Collection;

    /** The bus's last reading dated before $date, or null. */
    public function lastReadingBefore(int $busId, string $date): ?int;

    /** The bus's newest reading (by created_at), or null. */
    public function latestReading(int $busId): ?int;

    public function existsAt(int $busId, string $date, string $time): bool;

    /** Nearest reading before date+time (locked), skipping $exceptId. */
    public function previous(int $busId, string $date, string $time, ?int $exceptId = null): ?OdometerSubmission;

    /** Nearest reading after date+time (locked), skipping $exceptId. */
    public function next(int $busId, string $date, string $time, ?int $exceptId = null): ?OdometerSubmission;

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): OdometerSubmission;

    /** @param array<string, mixed> $attributes */
    public function update(OdometerSubmission $submission, array $attributes): void;

    public function delete(OdometerSubmission $submission): void;
}
