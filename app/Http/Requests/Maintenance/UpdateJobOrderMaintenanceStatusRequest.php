<?php

namespace App\Http\Requests\Maintenance;

use App\Enums\JobOrderRepairType;
use App\Enums\JobOrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobOrderMaintenanceStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'mechanic_names' => $this->normalizeMechanicNames($this->input('mechanic_names', [])),
            'repair_types' => $this->normalizeRepairTypes($this->input('repair_types', [])),
        ]);
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::enum(JobOrderStatus::class),
            ],
            'mechanic_names' => [
                Rule::requiredIf(fn (): bool => $this->input('status') === JobOrderStatus::Operational->value),
                'array',
                'max:20',
            ],
            'mechanic_names.*' => ['required', 'string', 'max:255', 'distinct:ignore_case'],
            'repair_types' => [
                Rule::requiredIf(fn (): bool => $this->input('status') === JobOrderStatus::Operational->value),
                'array',
                'max:5',
            ],
            'repair_types.*' => [
                'required',
                'string',
                'distinct',
                Rule::in(JobOrderRepairType::values()),
            ],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Maintenance status is required.',
            'status.enum' => 'Selected maintenance status is invalid.',
            'mechanic_names.required' => 'At least one mechanic is required before marking the unit Operational.',
            'mechanic_names.array' => 'Mechanic names must be submitted as a valid list.',
            'mechanic_names.*.required' => 'Mechanic name cannot be blank.',
            'mechanic_names.*.distinct' => 'Duplicate mechanic names are not allowed.',
            'mechanic_names.*.max' => 'Each mechanic name must not exceed 255 characters.',
            'repair_types.required' => 'Select at least one repair type before marking the unit Operational.',
            'repair_types.array' => 'Repair types must be submitted as a valid list.',
            'repair_types.*.in' => 'One or more selected repair types are invalid.',
            'remarks.max' => 'Remarks must not exceed 1000 characters.',
        ];
    }

    private function normalizeMechanicNames(mixed $mechanicNames): array
    {
        return collect(is_array($mechanicNames) ? $mechanicNames : [$mechanicNames])
            ->map(fn ($name): string => trim((string) $name))
            ->filter()
            ->unique(fn (string $name): string => mb_strtolower($name))
            ->values()
            ->all();
    }

    private function normalizeRepairTypes(mixed $repairTypes): array
    {
        return collect(is_array($repairTypes) ? $repairTypes : [$repairTypes])
            ->map(fn ($type): string => trim((string) $type))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
