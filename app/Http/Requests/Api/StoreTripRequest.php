<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

final class StoreTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'trip_code' => ['required', 'string', 'unique:trips,trip_code'],
            'bus_number' => ['required', 'string'],
            'driver_name' => ['required', 'string'],
        ];
    }
}
