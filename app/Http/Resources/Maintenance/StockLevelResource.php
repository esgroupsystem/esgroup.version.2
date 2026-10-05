<?php

declare(strict_types=1);

namespace App\Http\Resources\Maintenance;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One product row of the Maintenance Stock dashboard (`dashboards/maintenance-stock/index`),
 * after MaintenanceStockService added main_qty, balintawak_qty and transfer_suggestion.
 * `qty` is the quantity of the tab the row is shown in.
 *
 * @mixin Product
 */
final class StockLevelResource extends JsonResource
{
    public function __construct(Product $resource, private readonly string $qtyField = 'main_qty')
    {
        parent::__construct($resource);
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category->name ?? '—',
            'name' => $this->product_name,
            'details' => $this->details,
            'part_number' => $this->part_number,
            'unit' => $this->unit,
            'qty' => (int) $this->resource->getAttribute($this->qtyField),
            'main_qty' => (int) $this->main_qty,
            'balintawak_qty' => (int) $this->balintawak_qty,
            'suggestion' => $this->transfer_suggestion,
        ];
    }
}
