<?php

namespace App\Http\Requests\Payroll;

use App\Enums\WorkdayType;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveEmployeePlottingScheduleRequest extends FormRequest
{
    private const WEEKDAYS = [
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday',
    ];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $rows = $this->input('schedule', []);

        if (! is_array($rows)) {
            return;
        }

        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $dayOffs = $row['day_offs'] ?? [];

            if (is_string($dayOffs)) {
                $dayOffs = array_filter(array_map('trim', explode(',', $dayOffs)));
            }

            $rows[$index]['day_offs'] = array_values(array_unique(array_filter(
                is_array($dayOffs) ? $dayOffs : [],
                static fn (mixed $day): bool => is_string($day) && $day !== ''
            )));
        }

        $this->merge(['schedule' => $rows]);
    }

    public function rules(): array
    {
        return [
            'schedule' => ['nullable', 'array'],
            'schedule.*.employee_biometric_id' => [
                'required',
                'integer',
                'exists:employee_biometrics,id',
                'distinct',
            ],
            'schedule.*.status' => ['required', 'string', 'in:scheduled,rest_day,inactive'],
            'schedule.*.shift_name' => ['required', 'string', 'in:Regular Shift,Flexible Shift'],
            'schedule.*.workday_type' => ['required', Rule::enum(WorkdayType::class)],
            'schedule.*.time_in' => ['nullable', 'date_format:H:i'],
            'schedule.*.time_out' => ['nullable', 'date_format:H:i'],
            'schedule.*.grace_minutes' => ['nullable', 'integer', 'min:0', 'max:240'],
            'schedule.*.day_offs' => ['nullable', 'array', 'max:7'],
            'schedule.*.day_offs.*' => ['required', 'string', Rule::in(self::WEEKDAYS), 'distinct'],
            'schedule.*.remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach ((array) $this->input('schedule', []) as $index => $row) {
                if (! is_array($row)) {
                    continue;
                }

                $status = (string) ($row['status'] ?? 'scheduled');
                $shiftName = (string) ($row['shift_name'] ?? 'Regular Shift');
                $timeIn = $row['time_in'] ?? null;
                $timeOut = $row['time_out'] ?? null;

                if ($status !== 'scheduled' || $shiftName !== 'Regular Shift') {
                    continue;
                }

                if (blank($timeIn) || blank($timeOut)) {
                    $validator->errors()->add(
                        "schedule.{$index}.time_in",
                        'Regular Shift requires both Time In and Time Out.'
                    );

                    continue;
                }

                if ($timeIn === $timeOut) {
                    $validator->errors()->add(
                        "schedule.{$index}.time_out",
                        'Time In and Time Out cannot be the same.'
                    );

                    continue;
                }

                $workdayType = WorkdayType::tryFrom((string) ($row['workday_type'] ?? ''));

                if (! $workdayType) {
                    continue;
                }

                $clockMinutes = $this->clockMinutesBetween((string) $timeIn, (string) $timeOut);

                if ($clockMinutes !== $workdayType->clockMinutes()) {
                    $validator->errors()->add(
                        "schedule.{$index}.time_out",
                        sprintf(
                            '%s requires exactly %d clock hours (%d paid hour%s + 1 hour lunch). Current schedule span is %s.',
                            $workdayType->shortLabel(),
                            (int) ($workdayType->clockMinutes() / 60),
                            $workdayType->paidHours(),
                            $workdayType->paidHours() === 1 ? '' : 's',
                            $this->formatMinutes($clockMinutes)
                        )
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'schedule.*.employee_biometric_id.distinct' => 'An employee can only appear once in the submitted schedule.',
            'schedule.*.day_offs.max' => 'A maximum of seven weekly days off may be selected.',
            'schedule.*.day_offs.*.distinct' => 'Duplicate weekly day-off selections are not allowed.',
        ];
    }

    private function clockMinutesBetween(string $timeIn, string $timeOut): int
    {
        $start = Carbon::createFromFormat('H:i', $timeIn, 'Asia/Manila');
        $end = Carbon::createFromFormat('H:i', $timeOut, 'Asia/Manila');

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        return (int) $start->diffInMinutes($end);
    }

    private function formatMinutes(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return $remainingMinutes === 0
            ? "{$hours} hour(s)"
            : "{$hours} hour(s) and {$remainingMinutes} minute(s)";
    }
}
