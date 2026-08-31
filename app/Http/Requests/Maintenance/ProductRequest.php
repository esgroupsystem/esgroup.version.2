<?php

declare(strict_types=1);

namespace App\Http\Requests\Maintenance;

use Illuminate\Foundation\Http\FormRequest;

final class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->routeIs('items.store') ? 'items.create' : 'items.update';

        return $this->user()?->can($permission) === true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'product_name' => ['required', 'string', 'max:255'],
            'supplier_name' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:255'],
            'part_number' => ['nullable', 'string', 'max:255'],
            'details' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Category is required.',
            'category_id.exists' => 'Selected category does not exist.',
            'product_name.required' => 'Product name is required.',
            'product_name.max' => 'Product name must not exceed 255 characters.',
        ];
    }
}
