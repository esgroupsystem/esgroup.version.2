<?php

declare(strict_types=1);

namespace App\Http\Resources\Maintenance;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One row of Products → Categories (`products/categories/index`).
 *
 * @mixin Category
 */
final class CategoryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'products_count' => (int) $this->products_count,
            'update_url' => route('category.update', $this->id),
            'destroy_url' => route('category.destroy', $this->id),
        ];
    }
}
