<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

final class StoreCashierRemittanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'bus_number' => ['required', 'string'],
            'driver_name' => ['required', 'string'],
            'conductor_name' => ['required', 'string'],
            'dispatcher_name' => ['required', 'string'],
            'time_in' => ['required', 'date_format:H:i'],
            'time_out' => ['required', 'date_format:H:i'],
            'total_collection' => ['required', 'numeric', 'min:0'],
            'diesel' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
