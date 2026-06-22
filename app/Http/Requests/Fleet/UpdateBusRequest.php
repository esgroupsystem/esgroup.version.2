<?php

namespace App\Http\Requests\Fleet;

use App\Models\Bus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'bus_no' => $this->cleanRequired($this->input('bus_no')),
            'plate_no' => $this->cleanNullable($this->input('plate_no')),
            'company' => $this->cleanNullable($this->input('company')),
            'garage' => $this->cleanNullable($this->input('garage')),
            'chassis_number' => $this->cleanNullable($this->input('chassis_number')),
            'engine_number' => $this->cleanNullable($this->input('engine_number')),
            'case_number' => $this->cleanNullable($this->input('case_number')),
            'monitoring_remarks' => $this->cleanNullable($this->input('monitoring_remarks')),
        ]);
    }

    public function rules(): array
    {
        return [
            'bus_no' => [
                'required',
                'string',
                'max:50',
                Rule::unique('buses', 'bus_no')->ignore($this->route('bus')),
            ],

            'plate_no' => [
                'nullable',
                'string',
                'max:50',
            ],

            'company' => [
                'nullable',
                'string',
                'max:100',
            ],

            'garage' => [
                'nullable',
                'string',
                'max:100',
            ],

            'chassis_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'engine_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'case_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'operational_status' => [
                'required',
                'string',
                Rule::in(array_keys(Bus::operationalStatusOptions())),
            ],

            'sale_status' => [
                'required',
                'string',
                Rule::in(array_keys(Bus::saleStatusOptions())),
            ],

            'monitoring_remarks' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'bus_no' => 'bus number',
            'plate_no' => 'plate number',
            'operational_status' => 'condition',
            'sale_status' => 'sale status',
            'chassis_number' => 'chassis number',
            'engine_number' => 'engine number',
            'case_number' => 'case number',
            'monitoring_remarks' => 'remarks',
        ];
    }

    private function cleanRequired(mixed $value): string
    {
        return trim((string) $value);
    }

    private function cleanNullable(mixed $value): ?string
    {
        $cleanValue = trim((string) $value);

        return $cleanValue === '' ? null : $cleanValue;
    }
}
