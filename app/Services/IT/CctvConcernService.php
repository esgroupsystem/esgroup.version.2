<?php

declare(strict_types=1);

namespace App\Services\IT;

use App\Enums\CctvConcernStatus;
use App\Models\BusDetail;
use App\Models\CctvConcern;
use App\Models\ItInventoryItem;
use App\Models\User;
use App\Repositories\Contracts\Fleet\BusDetailRepositoryInterface;
use App\Repositories\Contracts\IT\CctvConcernRepositoryInterface;
use App\Repositories\Contracts\IT\ItInventoryItemRepositoryInterface;
use App\Repositories\Contracts\Security\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * CCTV Concern: list, counts, and create/update/delete with IT inventory stock kept in step.
 */
final class CctvConcernService
{
    public function __construct(
        private readonly CctvConcernRepositoryInterface $concerns,
        private readonly ItInventoryItemRepositoryInterface $inventory,
        private readonly BusDetailRepositoryInterface $buses,
        private readonly UserRepositoryInterface $users,
    ) {}

    /** An unknown status filter becomes "all". */
    public function normalizeStatus(string $status): string
    {
        return in_array($status, CctvConcernStatus::values(), true) ? $status : '';
    }

    /** @return LengthAwarePaginator<int, CctvConcern> */
    public function paginate(string $search, string $status): LengthAwarePaginator
    {
        return $this->concerns->paginate($search, $status);
    }

    /** @return Collection<int, CctvConcern> */
    public function exportRows(string $search, string $status): Collection
    {
        return $this->concerns->allMatching($search, $status);
    }

    /**
     * Counts for the cards above the list, over every concern matching the filters.
     *
     * @return array{total: int, open: int, progress: int, done: int, topIssue: ?string, topIssueCount: int, topPart: ?string, topPartCount: int, topAssignee: ?string, topAssigneeCount: int}
     */
    public function stats(string $search, string $status): array
    {
        $all = $this->concerns->allMatching($search, $status);
        $byStatus = $all->countBy('status');
        $top = static function (Collection $names): array {
            $counts = $names->filter()->countBy()->sortDesc();

            return [$counts->keys()->first(), (int) ($counts->first() ?? 0)];
        };

        [$topIssue, $topIssueCount] = $top($all->pluck('issue_type'));
        [$topPart, $topPartCount] = $top($all->flatMap(fn (CctvConcern $concern) => $concern->usedItems->map(fn ($used) => $used->inventoryItem->item_name ?? null)));
        [$topAssignee, $topAssigneeCount] = $top($all->map(fn (CctvConcern $concern): ?string => $concern->assignee?->full_name));

        return [
            'total' => $all->count(),
            'open' => (int) ($byStatus[CctvConcernStatus::Open->value] ?? 0),
            'progress' => (int) ($byStatus[CctvConcernStatus::InProgress->value] ?? 0),
            'done' => (int) ($byStatus[CctvConcernStatus::Fixed->value] ?? 0) + (int) ($byStatus[CctvConcernStatus::Closed->value] ?? 0),
            'topIssue' => $topIssue === null ? null : (string) $topIssue,
            'topIssueCount' => $topIssueCount,
            'topPart' => $topPart === null ? null : (string) $topPart,
            'topPartCount' => $topPartCount,
            'topAssignee' => $topAssignee === null ? null : (string) $topAssignee,
            'topAssigneeCount' => $topAssigneeCount,
        ];
    }

    /** @return Collection<int, BusDetail> */
    public function busOptions(): Collection
    {
        return $this->buses->options();
    }

    /** @return Collection<int, User> */
    public function agents(): Collection
    {
        return $this->users->withRoles(['IT Officer']);
    }

    /** @return Collection<int, ItInventoryItem> */
    public function inventoryOptions(): Collection
    {
        return $this->inventory->activeItems();
    }

    public function find(int $id): CctvConcern
    {
        return $this->concerns->findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data, User $actor): CctvConcern
    {
        return DB::transaction(function () use ($data, $actor): CctvConcern {
            $items = $data['items'] ?? [];
            unset($data['items']);

            $data['reported_by'] = $actor->full_name ?? 'System';
            $data['created_by'] = $actor->id;
            $data['jo_no'] = $this->nextJobOrderNumber();

            $concern = $this->concerns->create($data);
            $this->useItems($concern, is_array($items) ? $items : []);

            return $concern->refresh()->load('usedItems.inventoryItem');
        }, 3);
    }

    /**
     * Puts the old parts back in stock, then deducts the new list.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(CctvConcern $concern, array $data): CctvConcern
    {
        return DB::transaction(function () use ($concern, $data): CctvConcern {
            $locked = $this->concerns->findForUpdate($concern->id);

            $items = $data['items'] ?? [];
            unset($data['items']);

            $this->returnItems($locked);

            $status = CctvConcernStatus::from((string) $data['status']);
            $data['fixed_at'] = $status->isCompleted()
                ? ($locked->fixed_at ?: now())
                : null;

            $this->concerns->update($locked, $data);
            $this->useItems($locked, is_array($items) ? $items : []);

            return $locked->refresh()->load('usedItems.inventoryItem');
        }, 3);
    }

    /** Deletes the concern and puts its parts back in stock. */
    public function delete(CctvConcern $concern): void
    {
        DB::transaction(function () use ($concern): void {
            $locked = $this->concerns->findForUpdate($concern->id);

            $this->returnItems($locked);
            $this->concerns->delete($locked);
        }, 3);
    }

    /** JO-<year>-00001, counting up within the year. */
    private function nextJobOrderNumber(): string
    {
        $year = now()->year;
        $last = $this->concerns->lastNumberOfYear($year);
        $next = $last !== null ? ((int) substr($last, -5)) + 1 : 1;

        return "JO-{$year}-".str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    private function returnItems(CctvConcern $concern): void
    {
        foreach ($concern->usedItems as $usedItem) {
            $inventory = $this->inventory->findForUpdate((int) $usedItem->it_inventory_item_id);

            if ($inventory !== null) {
                $this->inventory->addStock($inventory, (int) $usedItem->qty_used);
            }
        }

        $this->concerns->deleteUsedItems($concern);
    }

    /** @param array<int, mixed> $items */
    private function useItems(CctvConcern $concern, array $items): void
    {
        $rows = [];
        $usedItemNames = [];

        foreach ($items as $row) {
            if (! is_array($row)) {
                continue;
            }

            $inventoryId = (int) ($row['it_inventory_item_id'] ?? 0);
            $qtyUsed = (int) ($row['qty_used'] ?? 0);

            if ($inventoryId <= 0 || $qtyUsed <= 0) {
                continue;
            }

            $inventory = $this->inventory->findForUpdate($inventoryId);
            if ($inventory === null) {
                throw new RuntimeException('A selected inventory item no longer exists.');
            }

            if ((int) $inventory->stock_qty < $qtyUsed) {
                throw new RuntimeException("Not enough stock for item: {$inventory->item_name}");
            }

            $this->inventory->removeStock($inventory, $qtyUsed);
            $rows[] = [
                'it_inventory_item_id' => $inventory->id,
                'qty_used' => $qtyUsed,
                'remarks' => $row['remarks'] ?? null,
            ];
            $usedItemNames[] = "{$inventory->item_name} x{$qtyUsed}";
        }

        $this->concerns->addUsedItems($concern, $rows);
        $this->concerns->update($concern, [
            'cctv_part' => $usedItemNames !== [] ? implode(', ', $usedItemNames) : null,
        ]);
    }
}
