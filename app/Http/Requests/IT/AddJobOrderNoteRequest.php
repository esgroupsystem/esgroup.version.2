<?php

declare(strict_types=1);

namespace App\Http\Requests\ITDepartment;

use Illuminate\Foundation\Http\FormRequest;

final class AddJobOrderNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('tickets.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string'],
            'details' => ['nullable', 'string'],
        ];
    }
}
