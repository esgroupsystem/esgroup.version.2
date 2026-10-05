<?php

declare(strict_types=1);

namespace App\Http\Requests\Scheduling;

use App\Models\Holiday;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Holiday Calendar create / edit form. */
final class HolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'actual_date' => ['required', 'date'],
            'observed_date' => ['required', 'date'],
            'holiday_type' => ['required', Rule::in(Holiday::TYPES)],
            'is_moved' => ['nullable', 'boolean'],
            'override_multipliers' => ['nullable', 'boolean'],
            'not_worked_multiplier' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'worked_multiplier' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'source_proclamation' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
