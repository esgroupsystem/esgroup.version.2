<?php

namespace App\Http\Requests\Fleet;

use App\Models\BusForSaleRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ForSaleUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'bus_id' => $this->bus_id ?: null,
            'bus_no' => trim((string) $this->bus_no),
            'plate_no' => $this->plate_no ? trim((string) $this->plate_no) : null,
            'company' => $this->company ? trim((string) $this->company) : null,
            'garage' => $this->garage ? trim((string) $this->garage) : null,
            'status' => $this->status ?: null,
        ]);
    }

    public function rules(): array
    {
        $recordId = $this->route('forSaleRecord')?->id;

        return [
            'bus_id' => [
                'nullable',
                'integer',
                'exists:buses,id',
                Rule::unique('bus_for_sale_records', 'bus_id')->ignore($recordId),
            ],

            'bus_no' => [
                'required',
                'string',
                'max:50',
            ],

            'plate_no' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:100'],
            'garage' => ['nullable', 'string', 'max:100'],

            'status' => [
                'required',
                'string',
                Rule::in(array_keys(BusForSaleRecord::statusOptions())),
            ],

            'storage_area' => ['nullable', 'string', 'max:150'],
            'breakdown_start_date' => ['nullable', 'date'],
            'breakdown_end_date' => ['nullable', 'date', 'after_or_equal:breakdown_start_date'],
            'column_11' => ['nullable', 'string', 'max:150'],
            'unit_location' => ['nullable', 'string', 'max:150'],
            'progress' => ['nullable', 'string', 'max:150'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'bus_id' => 'bus',
            'bus_no' => 'bus number',
            'plate_no' => 'plate number',
            'breakdown_start_date' => 'breakdown start date',
            'breakdown_end_date' => 'breakdown end date',
            'column_11' => 'column 11',
            'unit_location' => 'unit location',
        ];
    }
}
