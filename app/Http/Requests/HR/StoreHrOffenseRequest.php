<?php

declare(strict_types=1);

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

final class StoreHrOffenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('violations.create') === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'section' => ['required', 'string', 'max:255'],
            'offense_description' => ['required', 'string'],
            'offense_type' => ['required', 'string'],
            'offense_gravity' => ['required', 'string'],
        ];
    }
}
