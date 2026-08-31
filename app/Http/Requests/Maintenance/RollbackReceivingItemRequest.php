<?php

declare(strict_types=1);

namespace App\Http\Requests\Maintenance;

use Illuminate\Foundation\Http\FormRequest;

final class RollbackReceivingItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('receivings.rollback') === true;
    }

    public function rules(): array
    {
        return [
            'rollback_qty' => ['required', 'integer', 'min:1'],
        ];
    }
}
