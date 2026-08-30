<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

final class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'trip_id' => ['required', 'exists:trips,id'],
            'from_location' => ['required', 'string'],
            'to_location' => ['required', 'string'],
            'fare' => ['required', 'numeric', 'min:0'],
            'issued_at' => ['required', 'date'],
        ];
    }
}
