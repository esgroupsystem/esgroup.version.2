<?php

declare(strict_types=1);

namespace App\Http\Requests\Maintenance;

use App\Models\BusDetail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Bus List create and edit (allbus.store / allbus.update). Body and plate number are unique. */
final class BusDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $bus = $this->route('bus');
        $ignoreId = $bus instanceof BusDetail ? $bus->id : null;

        return [
            'garage' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'body_number' => ['required', 'string', 'max:255', Rule::unique('bus_details', 'body_number')->ignore($ignoreId)],
            'plate_number' => ['required', 'string', 'max:255', Rule::unique('bus_details', 'plate_number')->ignore($ignoreId)],
        ];
    }
}
