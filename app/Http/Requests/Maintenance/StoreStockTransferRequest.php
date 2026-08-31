<?php

declare(strict_types=1);

namespace App\Http\Requests\Maintenance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class StoreStockTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('stock-transfers.create') === true;
    }

    public function rules(): array
    {
        return [
            'from_location_id' => ['required', 'integer', 'exists:locations,id'],
            'to_location_id' => ['required', 'integer', 'exists:locations,id', 'different:from_location_id'],
            'transfer_date' => ['required', 'date'],
            'requested_by' => ['nullable', 'string', 'max:255'],
            'received_by' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
            'product_id' => ['required', 'array', 'min:1'],
            'product_id.*' => ['required', 'integer', 'exists:products,id', 'distinct'],
            'qty' => ['required', 'array', 'min:1'],
            'qty.*' => ['required', 'integer', 'min:1'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (count((array) $this->input('product_id', [])) !== count((array) $this->input('qty', []))) {
                    $validator->errors()->add('product_id', 'Product count and quantity count do not match.');
                }
            },
        ];
    }
}
