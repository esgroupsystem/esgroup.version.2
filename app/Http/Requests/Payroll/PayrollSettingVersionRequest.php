<?php

declare(strict_types=1);

namespace App\Http\Requests\Payroll;

use App\Support\Payroll\PayrollSettingCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/** Create or edit a Payroll Settings version. */
final class PayrollSettingVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'effective_from' => ['required', 'date_format:Y-m-d'],
            'label' => ['required', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:2000'],
            ...PayrollSettingCatalog::rules('values'),
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return PayrollSettingCatalog::attributes('values') + ['effective_from' => 'effective from'];
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [fn (Validator $validator) => self::checkValues($validator, 'values', (array) $this->input('values', []))];
    }

    /**
     * Checks between fields that single-field rules cannot do.
     *
     * @param  array<string, mixed>  $values
     */
    public static function checkValues(Validator $validator, string $prefix, array $values): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $number = fn (string $key): float => (float) ($values[$key] ?? 0);
        $checks = [
            'sss_maximum_msc' => [$number('sss_minimum_msc') <= $number('sss_maximum_msc'), 'The highest MSC must be at least the lowest MSC.'],
            'sss_maximum_range_start' => [$number('sss_first_middle_range') < $number('sss_maximum_range_start'), 'The highest-MSC pay must be above the lowest-MSC pay.'],
            'philhealth_ceiling' => [$number('philhealth_floor') <= $number('philhealth_ceiling'), 'The PhilHealth ceiling must be at least the floor.'],
            'scheduled_hours_per_day' => [$number('scheduled_hours_per_day') >= $number('paid_hours_per_day'), 'Scheduled clock hours cannot be less than paid hours.'],
        ];

        foreach ($checks as $key => [$ok, $message]) {
            if (! $ok) {
                $validator->errors()->add($prefix.'.'.$key, $message);
            }
        }
    }
}
