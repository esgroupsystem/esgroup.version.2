<?php

declare(strict_types=1);

namespace App\Http\Requests\Fleet;

use Illuminate\Foundation\Http\FormRequest;

/** Odometer Monitoring → edit a reading (odometer.update). */
final class UpdateOdometerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'date_bus_deployed' => ['nullable', 'date'],
            'date' => ['required', 'date'],
            'time' => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'driver_name' => ['required', 'string', 'max:255'],
            'new_odometer' => ['required', 'integer', 'min:0'],
            'diesel_consumption' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
