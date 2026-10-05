<?php

declare(strict_types=1);

namespace App\Http\Requests\ITDepartment;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateItInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('it-inventory.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'item_name' => ['required', 'string', 'max:255'],
            'category' => ['sometimes', 'nullable', 'string', 'max:100'],
            'brand' => ['sometimes', 'nullable', 'string', 'max:100'],
            'model' => ['sometimes', 'nullable', 'string', 'max:100'],
            'part_number' => ['sometimes', 'nullable', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:50'],
            'stock_qty' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'description' => ['sometimes', 'nullable', 'string'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
