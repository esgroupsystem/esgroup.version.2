<?php

declare(strict_types=1);

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;

/** Payroll Transaction Logs filters. */
final class PayrollAuditLogIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:150'],
            'module' => ['nullable', 'string', 'max:50'],
            'action' => ['nullable', 'string', 'max:50'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ];
    }

    /** @return array{search: string, module: string, action: string, user_id: string, date_from: string, date_to: string} */
    public function filters(): array
    {
        $valid = $this->validated();
        $filters = [];
        foreach (['search', 'module', 'action', 'user_id', 'date_from', 'date_to'] as $key) {
            $filters[$key] = trim((string) ($valid[$key] ?? ''));
        }

        /** @var array{search: string, module: string, action: string, user_id: string, date_from: string, date_to: string} $filters */
        return $filters;
    }
}
