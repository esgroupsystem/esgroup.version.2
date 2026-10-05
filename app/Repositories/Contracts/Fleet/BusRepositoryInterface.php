<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Fleet;

use App\Models\Bus;
use Illuminate\Support\Collection;

/**
 * Fleet buses (`buses`), used by Bus Analytics and Maintenance Job Orders. A bus is "for sale"
 * when a `bus_for_sale_records` row points to it (bus_id).
 */
interface BusRepositoryInterface
{
    /** @return Collection<int, Bus> id, bus_no, plate_no, company, garage by bus number */
    public function options(): Collection;

    /** @return Collection<int, Bus> with operational status and the last job order odometer, by bus number */
    public function optionsWithLastOdometer(): Collection;

    /** @return Collection<int, Bus> id, bus_no, plate_no, company, garage by bus no, plate, company, garage */
    public function pickerOptions(): Collection;

    public function find(int $id): ?Bus;

    public function findForUpdate(int $id): Bus;

    /** The only bus matching these fields (blank fields ignored), or null when none or several match. */
    public function findUnique(?string $busNo, ?string $plateNo, ?string $company, ?string $garage): ?Bus;

    /**
     * Buses matching the Bus Analytics filters (search, garage, company, operational_status, sale_status).
     *
     * @param  array<string, mixed>  $filters
     */
    public function countFiltered(array $filters): int;

    /**
     * Buses for the folder tabs (search, company, operational_status), Mirasol then Balintawak first.
     *
     * @param  array{search: string, company: string, operational_status: string}  $filters
     * @return Collection<int, Bus>
     */
    public function folderRows(array $filters): Collection;

    /** @return Collection<int, string> distinct garages, sorted */
    public function garages(): Collection;

    /** @return Collection<int, string> distinct companies, sorted */
    public function companies(): Collection;

    /**
     * Per garage or company: total_units, not_for_sale, mechanical_breakdown (open maintenance job
     * order), accident_related, on_hold, for_sale. Blank groups are "UNKNOWN".
     *
     * @param  'garage'|'company'  $column
     * @return Collection<int, object>
     */
    public function summaryBy(string $column): Collection;

    /**
     * @param  list<string>  $activeStatuses  operational_status values that count as running
     * @return array{total_units: int, for_sale: int, not_for_sale: int, mechanical_breakdown: int, accident_related: int, on_hold: int, active_for_sale: int}
     */
    public function counts(array $activeStatuses): array;

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Bus;

    public function save(Bus $bus): void;
}
