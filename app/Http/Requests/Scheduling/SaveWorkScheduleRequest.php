<?php

declare(strict_types=1);

namespace App\Http\Requests\Scheduling;

use App\Enums\WorkdayType;
use App\Models\EmployeePlottingSchedule;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class SaveWorkScheduleRequest extends FormRequest
{
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
            'schedule.*.status' => ['required', 'string', Rule::in(EmployeePlottingSchedule::STATUSES)],
            'schedule.*.shift_name' => ['required', 'string', Rule::in(EmployeePlottingSchedule::SHIFTS)],
            'schedule.*.flexible_mode' => ['nullable', 'string', Rule::in(EmployeePlottingSchedule::FLEXIBLE_MODES)],
            'schedule.*.workday_type' => ['required', Rule::enum(WorkdayType::class)],
            'schedule.*.time_in' => ['nullable', 'date_format:H:i'],
            'schedule.*.time_out' => ['nullable', 'date_format:H:i'],
            'schedule.*.grace_minutes' => ['nullable', 'integer', 'min:0', 'max:240'],
            'schedule.*.day_offs' => ['nullable', 'array', 'max:7'],
            // No 'distinct' here: with the nested wildcard Laravel compares day-offs
            // across every employee row, so two employees could not share a day off.
            // Duplicates within one row are already removed in prepareForValidation().
            'schedule.*.day_offs.*' => ['required', 'string', Rule::in(EmployeePlottingSchedule::WEEKDAYS)],
            'schedule.*.remarks' => ['nullable', 'string', 'max:255'],
            // "Different time per day": weekday => time in / out + work hours.
            'schedule.*.weekly_times' => ['nullable', 'array', 'max:7'],
            'schedule.*.weekly_times.*' => ['array'],
            'schedule.*.weekly_times.*.time_in' => ['required', 'date_format:H:i'],
            'schedule.*.weekly_times.*.time_out' => ['required', 'date_format:H:i'],
            'schedule.*.weekly_times.*.workday_type' => ['required', Rule::enum(WorkdayType::class)],
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
                $flexibleModeInput = (string) ($row['flexible_mode'] ?? EmployeePlottingSchedule::FLEXIBLE_MODE_ANYTIME);
                $flexibleMode = in_array($flexibleModeInput, EmployeePlottingSchedule::FLEXIBLE_MODES, true)
                    ? $flexibleModeInput
                    : EmployeePlottingSchedule::FLEXIBLE_MODE_ANYTIME;
                $timeIn = $row['time_in'] ?? null;
                $timeOut = $row['time_out'] ?? null;

                if ($status !== 'scheduled') {
                    continue;
                }

                $isFlexible = $shiftName === EmployeePlottingSchedule::FLEXIBLE_SHIFT;

                // Flexible Shift (Condition): just needs a clock-in window, not an exact clock-hour span.
                if ($isFlexible && $flexibleMode === EmployeePlottingSchedule::FLEXIBLE_MODE_CONDITION) {
                    if (blank($timeIn) || blank($timeOut)) {
                        $validator->errors()->add(
                            "schedule.{$index}.time_in",
                            'Flexible Shift (Condition) requires a clock-in window start and end.'
                        );

                        continue;
                    }

                    if ($timeIn === $timeOut) {
                        $validator->errors()->add(
                            "schedule.{$index}.time_out",
                            'Clock-in window start and end cannot be the same.'
                        );
                    }

                    continue;
                }

                // Flexible Shift (Anytime): no time fields to validate.
                if ($isFlexible && $flexibleMode === EmployeePlottingSchedule::FLEXIBLE_MODE_ANYTIME) {
                    continue;
                }

                // Regular Shift and Flexible Shift (Custom) must span exactly the workday's clock hours.

                // Each weekday of a "different time per day" schedule follows the same span rule.
                foreach ((array) ($row['weekly_times'] ?? []) as $day => $times) {
                    if (! in_array($day, EmployeePlottingSchedule::WEEKDAYS, true)) {
                        $validator->errors()->add("schedule.{$index}.weekly_times", "Unknown weekday \"{$day}\".");

                        continue;
                    }

                    $this->checkSpan(
                        $validator,
                        "schedule.{$index}.weekly_times.{$day}.time_out",
                        $times['time_in'] ?? null,
                        $times['time_out'] ?? null,
                        WorkdayType::tryFrom((string) ($times['workday_type'] ?? '')),
                        "{$day}: "
                    );
                }

                if (blank($timeIn) || blank($timeOut)) {
                    $validator->errors()->add(
                        "schedule.{$index}.time_in",
                        $isFlexible
                            ? 'Flexible Shift (Custom) requires both Time In and Time Out.'
                            : 'Regular Shift requires both Time In and Time Out.'
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
                            '%s requires exactly %d clock hours (%d paid hours%s). Current schedule span is %s.',
                            $workdayType->shortLabel(),
                            (int) ($workdayType->clockMinutes() / 60),
                            $workdayType->paidHours(),
                            $workdayType->lunchMinutes() > 0 ? ' + 1 hour lunch' : ', no lunch break',
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
        ];
    }

    private function checkSpan(Validator $validator, string $key, mixed $timeIn, mixed $timeOut, ?WorkdayType $type, string $prefix): void
    {
        if (blank($timeIn) || blank($timeOut) || $type === null || ! preg_match('/^\d{2}:\d{2}$/', (string) $timeIn) || ! preg_match('/^\d{2}:\d{2}$/', (string) $timeOut)) {
            return;
        }

        if ($timeIn === $timeOut) {
            $validator->errors()->add($key, $prefix.'Time In and Time Out cannot be the same.');

            return;
        }

        $clockMinutes = $this->clockMinutesBetween((string) $timeIn, (string) $timeOut);

        if ($clockMinutes !== $type->clockMinutes()) {
            $validator->errors()->add($key, sprintf(
                '%s%s requires exactly %d clock hours. Current span is %s.',
                $prefix,
                $type->shortLabel(),
                (int) ($type->clockMinutes() / 60),
                $this->formatMinutes($clockMinutes)
            ));
        }
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
