<?php

namespace App\Http\Requests\Fleet;

use App\Models\Bus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'bus_no' => mb_strtoupper(trim((string) $this->bus_no)),
            'plate_no' => $this->plate_no ? mb_strtoupper(trim((string) $this->plate_no)) : null,
            'company' => $this->company ? mb_strtoupper(trim((string) $this->company)) : null,
            'garage' => $this->garage ? mb_strtoupper(trim((string) $this->garage)) : null,
            'chassis_number' => $this->chassis_number ? mb_strtoupper(trim((string) $this->chassis_number)) : null,
            'engine_number' => $this->engine_number ? mb_strtoupper(trim((string) $this->engine_number)) : null,
            'case_number' => $this->case_number ? mb_strtoupper(trim((string) $this->case_number)) : null,
            'operational_status' => $this->operational_status ?: Bus::STATUS_ACTIVE,
            'sale_status' => $this->sale_status ?: Bus::SALE_NOT_FOR_SALE,
            'monitoring_remarks' => $this->monitoring_remarks ? trim((string) $this->monitoring_remarks) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'bus_no' => [
                'required',
                'string',
                'max:50',
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
                Rule::in(array_keys(Bus::operationalStatusOptions())),
            ],
            'sale_status' => [
                'required',
                Rule::in(array_keys(Bus::saleStatusOptions())),
            ],
            'monitoring_remarks' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}
