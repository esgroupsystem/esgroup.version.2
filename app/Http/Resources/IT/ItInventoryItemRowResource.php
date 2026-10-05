<?php

declare(strict_types=1);

namespace App\Http\Resources\IT;

use App\Models\ItInventoryItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * One row of the IT Inventory list (`it/inventory/index`).
 *
 * @mixin ItInventoryItem
 */
final class ItInventoryItemRowResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'item_name' => $this->item_name,
            'description' => $this->description ? Str::limit($this->description, 65) : null,
            'category' => $this->category ?: 'Uncategorized',
            'brand' => $this->brand,
            'model' => $this->model,
            'part_number' => $this->part_number,
            'stock_qty' => (int) $this->stock_qty,
            'minimum_stock' => (int) $this->minimum_stock,
            'unit' => $this->unit,
            'location' => $this->location,
            'is_active' => (bool) $this->is_active,
            'edit_url' => route('it-inventory.edit', $this->id),
            'destroy_url' => route('it-inventory.destroy', $this->id),
        ];
    }
}
