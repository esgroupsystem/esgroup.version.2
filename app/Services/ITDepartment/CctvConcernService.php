<?php

declare(strict_types=1);

namespace App\Services\ITDepartment;

use App\Enums\CctvConcernStatus;
use App\Models\CctvConcern;
use App\Models\CctvConcernItem;
use App\Models\ItInventoryItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class CctvConcernService
{
    /** @param array<string, mixed> $data */
    public function create(array $data, User $actor): CctvConcern
    {
        return DB::transaction(function () use ($data, $actor): CctvConcern {
            $items = $data['items'] ?? [];
            unset($data['items']);

            $data['reported_by'] = $actor->full_name ?? 'System';
            $data['created_by'] = $actor->id;
            $data['jo_no'] = $this->nextJobOrderNumber();

            $jobOrder = CctvConcern::query()->create($data);
            $this->replaceItems($jobOrder, is_array($items) ? $items : []);

            return $jobOrder->refresh()->load('usedItems.inventoryItem');
        }, 3);
    }

    /** @param array<string, mixed> $data */
    public function update(CctvConcern $jobOrder, array $data): CctvConcern
    {
        return DB::transaction(function () use ($jobOrder, $data): CctvConcern {
            $locked = CctvConcern::query()
                ->with('usedItems')
                ->lockForUpdate()
                ->findOrFail($jobOrder->id);

            $items = $data['items'] ?? [];
            unset($data['items']);

            $this->restoreExistingItems($locked);

            $status = CctvConcernStatus::from((string) $data['status']);
            $data['fixed_at'] = $status->isCompleted()
                ? ($locked->fixed_at ?: now())
                : null;

            $locked->update($data);
            $this->replaceItems($locked, is_array($items) ? $items : []);

            return $locked->refresh()->load('usedItems.inventoryItem');
        }, 3);
    }

    public function delete(CctvConcern $jobOrder): void
    {
        DB::transaction(function () use ($jobOrder): void {
            $locked = CctvConcern::query()
                ->with('usedItems')
                ->lockForUpdate()
                ->findOrFail($jobOrder->id);

            $this->restoreExistingItems($locked);
            $locked->delete();
        }, 3);
    }

    private function nextJobOrderNumber(): string
    {
        $year = now()->year;
        $last = CctvConcern::query()
            ->where('jo_no', 'like', "JO-{$year}-%")
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $next = $last ? ((int) substr((string) $last->jo_no, -5)) + 1 : 1;

        return "JO-{$year}-".str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    private function restoreExistingItems(CctvConcern $jobOrder): void
    {
        foreach ($jobOrder->usedItems as $usedItem) {
            $inventory = ItInventoryItem::query()
                ->lockForUpdate()
                ->find($usedItem->it_inventory_item_id);

            if ($inventory !== null) {
                $inventory->increment('stock_qty', (int) $usedItem->qty_used);
            }
        }

        $jobOrder->usedItems()->delete();
    }

    /** @param array<int, mixed> $items */
    private function replaceItems(CctvConcern $jobOrder, array $items): void
    {
        $savedItems = [];
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

            $inventory = ItInventoryItem::query()->lockForUpdate()->findOrFail($inventoryId);

            if ((int) $inventory->stock_qty < $qtyUsed) {
                throw new RuntimeException("Not enough stock for item: {$inventory->item_name}");
            }

            $inventory->decrement('stock_qty', $qtyUsed);
            $savedItems[] = new CctvConcernItem([
                'it_inventory_item_id' => $inventory->id,
                'qty_used' => $qtyUsed,
                'remarks' => $row['remarks'] ?? null,
            ]);
            $usedItemNames[] = "{$inventory->item_name} x{$qtyUsed}";
        }

        if ($savedItems !== []) {
            $jobOrder->usedItems()->saveMany($savedItems);
        }

        $jobOrder->update([
            'cctv_part' => $usedItemNames !== [] ? implode(', ', $usedItemNames) : null,
        ]);
    }
}
