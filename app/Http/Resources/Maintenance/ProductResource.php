<?php

declare(strict_types=1);

namespace App\Http\Resources\Maintenance;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One row of Products → Products (`products/items/index`), both the item and the stock list.
 *
 * @mixin Product
 */
final class ProductResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => (string) $this->category_id,
            'category' => $this->category?->name,
            'product_name' => $this->product_name,
            'supplier_name' => $this->supplier_name,
            'unit' => $this->unit,
            'part_number' => $this->part_number,
            'details' => $this->details,
            'stock_qty' => (int) ($this->stock_qty ?? 0),
            'update_url' => route('items.update', $this->id),
            'destroy_url' => route('items.destroy', $this->id),
        ];
    }
}
