<?php

declare(strict_types=1);

namespace App\Http\Requests\Maintenance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class StorePartsOutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('parts-out.create') === true;
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => ['nullable', 'integer', 'exists:bus_details,id'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'mechanic_name' => ['required', 'string', 'max:255'],
            'requested_by' => ['nullable', 'string', 'max:255'],
            'issued_date' => ['required', 'date'],
            'job_order_no' => ['nullable', 'string', 'max:255'],
            'odometer' => ['nullable', 'string', 'max:255'],
            'purpose' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
            'product_id' => ['required', 'array', 'min:1'],
            'product_id.*' => ['required', 'integer', 'exists:products,id', 'distinct'],
            'qty_used' => ['required', 'array', 'min:1'],
            'qty_used.*' => ['required', 'integer', 'min:1'],
            'item_remarks' => ['nullable', 'array'],
            'item_remarks.*' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (count((array) $this->input('product_id', [])) !== count((array) $this->input('qty_used', []))) {
                    $validator->errors()->add('product_id', 'Product count and quantity count do not match.');
                }

                $userLocationId = $this->user()?->location_id;
                if ($userLocationId && (int) $this->input('location_id') !== (int) $userLocationId) {
                    $validator->errors()->add('location_id', 'You are only allowed to issue parts from your assigned garage.');
                }
            },
        ];
    }
}
