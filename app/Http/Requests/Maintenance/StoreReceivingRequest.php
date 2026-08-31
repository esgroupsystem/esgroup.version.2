<?php

declare(strict_types=1);

namespace App\Http\Requests\Maintenance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class StoreReceivingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('receivings.create') === true;
    }

    public function rules(): array
    {
        return [
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'delivered_by' => ['required', 'string', 'max:255'],
            'delivery_date' => ['required', 'date'],
            'remarks' => ['nullable', 'string', 'max:5000'],
            'proof_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'product_id' => ['required', 'array', 'min:1'],
            'product_id.*' => ['required', 'integer', 'exists:products,id', 'distinct'],
            'qty_delivered' => ['required', 'array', 'min:1'],
            'qty_delivered.*' => ['required', 'integer', 'min:1'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (count((array) $this->input('product_id', [])) !== count((array) $this->input('qty_delivered', []))) {
                    $validator->errors()->add('product_id', 'Product count and quantity count do not match.');
                }

                $userLocationId = $this->user()?->location_id;
                if ($userLocationId && (int) $this->input('location_id') !== (int) $userLocationId) {
                    $validator->errors()->add('location_id', 'You are only allowed to receive items for your assigned garage.');
                }
            },
        ];
    }
}
