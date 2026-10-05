<?php

declare(strict_types=1);

namespace App\Services\Fleet;

use App\Models\BusDetail;
use App\Models\DieselStock;
use App\Models\OdometerSubmission;
use App\Repositories\Contracts\Fleet\BusDetailRepositoryInterface;
use App\Repositories\Contracts\Fleet\DieselStockRepositoryInterface;
use App\Repositories\Contracts\Fleet\OdometerSubmissionRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Fleet → Odometer Monitoring: km run per reading (against the bus's previous reading), diesel
 * used, km per liter, change-oil countdown, and the diesel stock (in − out + adjustment).
 * A reading must sit between the bus's previous and next readings.
 */
final class OdometerService
{
    public const CHANGE_OIL_EVERY_KM = 10000;

    public function __construct(
        private readonly OdometerSubmissionRepositoryInterface $submissions,
        private readonly DieselStockRepositoryInterface $diesel,
        private readonly BusDetailRepositoryInterface $buses,
    ) {}

    /** @return Collection<int, BusDetail> by body then plate number */
    public function busOptions(): Collection
    {
        return $this->buses->options();
    }

    /**
     * Every reading of the period with its computed figures, ordered by bus, date and time.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function records(OdometerPeriod $period, ?int $busId, ?int $lastChangeOilKm): Collection
    {
        $rows = $this->submissions->forPeriod($busId, $period->from(), $period->to());

        $previous = $rows->pluck('bus_detail_id')->filter()->unique()
            ->mapWithKeys(fn (int|string $id): array => [(int) $id => $this->submissions->lastReadingBefore((int) $id, $period->from())])
            ->all();

        return $rows->map(function (OdometerSubmission $row) use (&$previous, $lastChangeOilKm): array {
            $busId = (int) $row->bus_detail_id;
            $odometer = (int) $row->new_odometer;
            $before = $previous[$busId] ?? null;
            $kmRun = $before !== null ? $odometer - $before : 0;
            $liters = (float) ($row->diesel_consumption ?? 0);
            $previous[$busId] = $odometer;

            return [
                'id' => (int) $row->id,
                'bus_detail_id' => $busId,
                'garage' => $row->getAttribute('garage'),
                'bus_name' => $row->getAttribute('bus_name'),
                'body_number' => $row->getAttribute('body_number'),
                'plate_number' => $row->getAttribute('plate_number'),
                'date' => $row->date,
                'time' => $row->time,
                'driver_name' => $row->driver_name ?: 'N/A',
                // Raw value for the edit form.
                'driver_name_raw' => $row->driver_name,
                'date_bus_deployed' => $row->date_bus_deployed ?? null,
                'previous_odometer' => $before,
                'new_odometer' => $odometer,
                'total_km_run' => $kmRun,
                'diesel_consumption' => $liters,
                'km_per_liter' => $liters > 0 && $kmRun > 0 ? $kmRun / $liters : 0,
                'remaining_change_oil' => ($lastChangeOilKm ?: $odometer) + self::CHANGE_OIL_EVERY_KM - $odometer,
            ];
        });
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $records
     * @return array{current_stock: float, period_in: float, period_out: float, period_adjustment: float, total_km: int, total_liters: float, average_km_per_liter: float}
     */
    public function summary(OdometerPeriod $period, Collection $records): array
    {
        $km = (int) $records->sum('total_km_run');
        $liters = (float) $records->sum('diesel_consumption');

        return [
            'current_stock' => $this->diesel->liters('in') - $this->diesel->liters('out') + $this->diesel->liters('adjustment'),
            'period_in' => $this->diesel->liters('in', $period->from(), $period->to()),
            'period_out' => $this->diesel->liters('out', $period->from(), $period->to()),
            'period_adjustment' => $this->diesel->liters('adjustment', $period->from(), $period->to()),
            'total_km' => $km,
            'total_liters' => $liters,
            'average_km_per_liter' => $liters > 0 ? $km / $liters : 0,
        ];
    }

    /** @return Collection<int, DieselStock> */
    public function movements(OdometerPeriod $period, ?int $busId): Collection
    {
        return $this->diesel->movements($busId, $period->from(), $period->to());
    }

    /** @param array<string, mixed> $data validated StoreDieselStockRequest */
    public function storeDieselStock(array $data, ?int $userId): DieselStock
    {
        return $this->diesel->create([
            ...$data,
            'total_cost' => ! empty($data['unit_cost']) ? (float) $data['liters'] * (float) $data['unit_cost'] : null,
            'encoded_by' => $userId,
        ]);
    }

    /**
     * Saves a manual reading, optionally with a matching diesel "out" movement.
     *
     * @param  array<string, mixed>  $data  validated StoreManualOdometerRequest
     * @return string|null the refusal message, or null when saved
     */
    public function storeManual(array $data, bool $deductDiesel, ?int $userId): ?string
    {
        $date = Carbon::parse($data['date'])->toDateString();
        $time = Carbon::parse($data['time'])->format('H:i:s');
        $odometer = (int) $data['new_odometer'];
        $busId = (int) $data['bus_detail_id'];
        $liters = (float) ($data['diesel_consumption'] ?? 0);

        return DB::transaction(function () use ($data, $date, $time, $odometer, $busId, $liters, $deductDiesel, $userId): ?string {
            if ($this->submissions->existsAt($busId, $date, $time)) {
                return 'This bus already has an odometer record with the same date and time. Adjust the time by at least 1 minute.';
            }

            $previous = $this->submissions->previous($busId, $date, $time);
            if ($previous && $odometer < (int) $previous->new_odometer) {
                return 'New odometer cannot be lower than the previous odometer reading of '.number_format((int) $previous->new_odometer).' km.';
            }

            $next = $this->submissions->next($busId, $date, $time);
            if ($next && $odometer > (int) $next->new_odometer) {
                return 'New odometer cannot be higher than the next odometer reading of '.number_format((int) $next->new_odometer).' km on '
                    .Carbon::parse($next->date)->format('M d, Y').' '.Carbon::parse($next->time)->format('g:i A').'.';
            }

            $submission = $this->submissions->create([
                'user_id' => $userId,
                'bus_detail_id' => $busId,
                'new_odometer' => $odometer,
                'driver_name' => $data['driver_name'] ?? null,
                'diesel_consumption' => $liters,
                'date_bus_deployed' => $data['date_bus_deployed'] ?? $date,
                'date' => $date,
                'time' => $time,
            ]);

            if ($deductDiesel && $liters > 0) {
                $this->diesel->create([
                    'date' => $date,
                    'type' => 'out',
                    'liters' => $liters,
                    'unit_cost' => null,
                    'total_cost' => null,
                    'bus_detail_id' => $busId,
                    'reference_no' => 'ODO-'.$submission->id,
                    'remarks' => 'Auto diesel OUT from manual odometer encoding.',
                    'encoded_by' => $userId,
                ]);
            }

            return null;
        });
    }

    /**
     * @param  array<string, mixed>  $data  validated UpdateOdometerRequest
     *
     * @throws ValidationException when the reading falls outside its neighbours
     */
    public function update(OdometerSubmission $submission, array $data): void
    {
        DB::transaction(function () use ($submission, $data): void {
            $busId = (int) ($submission->bus_detail_id ?? 0);
            // The reading itself is skipped, so moving it to another date compares it with its real neighbours.
            $previous = $this->submissions->previous($busId, $data['date'], $data['time'], (int) $submission->id);
            $next = $this->submissions->next($busId, $data['date'], $data['time'], (int) $submission->id);

            if ($previous && $data['new_odometer'] < $previous->new_odometer) {
                throw ValidationException::withMessages(['new_odometer' => 'Odometer cannot be lower than previous reading.']);
            }
            if ($next && $data['new_odometer'] > $next->new_odometer) {
                throw ValidationException::withMessages(['new_odometer' => 'Odometer cannot exceed next reading.']);
            }

            $this->submissions->update($submission, [
                'date_bus_deployed' => $data['date_bus_deployed'] ?? null,
                'date' => $data['date'],
                'time' => $data['time'],
                'driver_name' => $data['driver_name'] ?? null,
                'new_odometer' => $data['new_odometer'],
                'diesel_consumption' => $data['diesel_consumption'] ?? 0,
            ]);
            $this->diesel->updateOdometerDeduction((int) $submission->id, (float) ($data['diesel_consumption'] ?? 0), $data['date']);
        });
    }

    /** Deletes the reading and its automatic diesel "out" row. */
    public function delete(OdometerSubmission $submission): void
    {
        DB::transaction(function () use ($submission): void {
            $this->diesel->deleteOdometerDeduction((int) $submission->id, $submission->bus_detail_id !== null ? (int) $submission->bus_detail_id : null);
            $this->submissions->delete($submission);
        });
    }

    /** Last reading of a bus for the mobile app (0 when none). */
    public function latestReading(BusDetail $bus): int
    {
        return $this->submissions->latestReading((int) $bus->getKey()) ?? 0;
    }
}
