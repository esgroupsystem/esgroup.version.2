<?php

declare(strict_types=1);

namespace App\Http\Requests\ITDepartment;

use App\Enums\CctvConcernStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreCctvConcernRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('cctv.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'bus_no' => ['required', 'integer', 'exists:bus_details,id'],
            'issue_type' => ['required', 'string', 'max:80'],
            'problem_details' => ['required', 'string'],
            'status' => ['required', Rule::enum(CctvConcernStatus::class)],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'items' => ['nullable', 'array'],
            'items.*.it_inventory_item_id' => ['nullable', 'integer', 'exists:it_inventory_items,id'],
            'items.*.qty_used' => ['nullable', 'integer', 'min:1'],
            'items.*.remarks' => ['nullable', 'string', 'max:255'],
        ];
    }
}
