<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

final class StoreOdometerSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'bus_detail_id' => ['required', 'exists:bus_details,id'],
            'new_odometer' => ['required', 'numeric'],
            'diesel_consumption' => ['required', 'numeric', 'min:0'],
            'driver_name' => ['required', 'string'],
            'date_bus_deployed' => ['required', 'date'],
            'date' => ['required', 'date'],
            'time' => ['required'],
        ];
    }
}
