<?php

declare(strict_types=1);

namespace App\Http\Requests\Fleet;

use Illuminate\Foundation\Http\FormRequest;

/** Odometer Monitoring → manual reading (odometer.manual.store). */
final class StoreManualOdometerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'bus_detail_id' => ['required', 'exists:bus_details,id'],
            'date_bus_deployed' => ['nullable', 'date'],
            'date' => ['required', 'date'],
            'time' => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            // odometer_submissions.driver_name is NOT NULL.
            'driver_name' => ['required', 'string', 'max:255'],
            'new_odometer' => ['required', 'integer', 'min:0'],
            'diesel_consumption' => ['nullable', 'numeric', 'min:0'],
            'also_deduct_diesel_stock' => ['nullable', 'boolean'],
        ];
    }
}
