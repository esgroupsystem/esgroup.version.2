<?php

namespace App\Http\Requests\Maintenance;

use App\Enums\JobOrderRepairType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJobOrderMaintenanceRequest extends FormRequest
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
            'job_order_no' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9\-\/]+$/',
                Rule::unique('job_orders_maintenance', 'job_order_no'),
            ],
            'bus_id' => ['required', 'integer', 'exists:buses,id'],
            'full_name' => ['nullable', 'string', 'max:255'],
            'mechanic_names' => ['nullable', 'array', 'max:20'],
            'mechanic_names.*' => ['required', 'string', 'max:255', 'distinct:ignore_case'],
            'repair_types' => ['nullable', 'array', 'max:5'],
            'repair_types.*' => [
                'required',
                'string',
                'distinct',
                Rule::in(JobOrderRepairType::values()),
            ],
            'description_of_work' => ['required', 'string', 'min:5', 'max:10000'],
            'odometer_reading' => ['nullable', 'integer', 'min:0', 'max:9999999'],
        ];
    }

    public function messages(): array
    {
        return [
            'job_order_no.unique' => 'This job order number is already used.',
            'job_order_no.regex' => 'Job order number may only contain letters, numbers, dash, and slash.',
            'bus_id.required' => 'Please select a bus.',
            'bus_id.exists' => 'The selected bus does not exist.',
            'mechanic_names.array' => 'Mechanic names must be submitted as a valid list.',
            'mechanic_names.max' => 'A maximum of 20 mechanics may be assigned.',
            'mechanic_names.*.required' => 'Mechanic name cannot be blank.',
            'mechanic_names.*.distinct' => 'Duplicate mechanic names are not allowed.',
            'mechanic_names.*.max' => 'Each mechanic name must not exceed 255 characters.',
            'repair_types.array' => 'Repair types must be submitted as a valid list.',
            'repair_types.*.in' => 'One or more selected repair types are invalid.',
            'description_of_work.required' => 'Description of work is required.',
            'description_of_work.min' => 'Description of work must be at least 5 characters.',
            'odometer_reading.integer' => 'Odometer reading must be a valid number.',
            'odometer_reading.min' => 'Odometer reading cannot be negative.',
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
