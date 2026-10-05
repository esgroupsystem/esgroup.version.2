<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Fleet;

use App\Models\BusForSaleRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/** For-sale units (`bus_for_sale_records`), kept in sync with `buses.sale_status`. */
interface BusForSaleRecordRepositoryInterface
{
    /**
     * For Sale Units list: longest breakdown first. Filters: search, company, garage, status.
     *
     * @param  array{search: string, company: string, garage: string, status: string}  $filters
     * @return LengthAwarePaginator<int, BusForSaleRecord>
     */
    public function paginate(array $filters, int $perPage = 25): LengthAwarePaginator;

    /**
     * Folder tab rows (search, company), by company then bus number.
     *
     * @param  array{search: string, company: string}  $filters
     * @return Collection<int, BusForSaleRecord>
     */
    public function folderRows(array $filters): Collection;

    public function count(): int;

    /** @return Collection<string, int> status => count */
    public function countByStatus(): Collection;

    /** @return Collection<int, object{company_name: string, status: string, total: int}> per company and status; blank company is "UNKNOWN" */
    public function countByCompanyAndStatus(): Collection;

    /** @return Collection<int, string> */
    public function companies(): Collection;

    /** @return Collection<int, string> */
    public function garages(): Collection;

    public function forBus(int $busId): ?BusForSaleRecord;

    public function existsForBus(int $busId): bool;

    /** Deletes the bus's records, except $keepId. */
    public function deleteForBus(int $busId, ?int $keepId = null): void;

    public function save(BusForSaleRecord $record): void;

    /** Links the record to a bus without touching updated_at or firing events. */
    public function linkBus(BusForSaleRecord $record, int $busId): void;

    public function delete(BusForSaleRecord $record): void;
}
