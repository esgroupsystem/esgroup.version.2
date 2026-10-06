<?php

declare(strict_types=1);

namespace App\Support\Payroll;

/**
 * Every payroll number that can be changed from Payroll Settings.
 *
 * Each field writes one or more config keys that the payroll engine reads (`payroll.*`, `sss.*`).
 * Values are stored in config units: a "percent" field holds 1.25 for 125% and 0.05 for 5%.
 * The starting value of every field is the shipped config file (config/payroll.php,
 * config/sss.php), so an empty settings table computes exactly like before.
 *
 * Field shape: key, label, type, help, targets (config path => transform), plus
 * min / max / step for numbers and options for selects.
 * Types: percent, money, number, hours, minutes, days, time, select, text, date.
 * Transforms: value (as is), minutes (hours × 60), map (select option => value), sum (adds fields).
 */
final class PayrollSettingCatalog
{
    private const BASIS_OPTIONS = [
        'actual_cycle_basic' => 'Actual gross for the month (both cutoffs)',
        'fixed_monthly_basic' => 'Fixed monthly basic salary',
        'none' => 'Do not compute',
    ];

    private const SCHEDULE_OPTIONS = [
        'second_cutoff' => '1st cutoff (26-10)',
        'first_cutoff' => '2nd cutoff (11-25)',
        'every_cutoff' => 'Split across both cutoffs',
        'none' => 'Do not deduct',
    ];

    private const HOLIDAY_QUALIFICATION_OPTIONS = [
        'day_before' => 'Worked (or on leave / day off) the day before',
        'day_before_and_after' => 'Worked the day before and the day after',
        'none' => 'Always paid, no check',
    ];

    /** @var array<string, array<string, mixed>>|null */
    private static ?array $fields = null;

    /** @var array<string, array<string, mixed>> */
    private static array $factory = [];

    /**
     * Sections in display order.
     *
     * @return list<array{key: string, title: string, description: string, fields: list<array<string, mixed>>}>
     */
    public static function sections(): array
    {
        return [
            [
                'key' => 'attendance',
                'title' => 'Work day & attendance',
                'description' => 'Paid hours, break, grace periods and how late / undertime minutes are rounded. Work Schedule can still set 8 or 9 paid hours per employee.',
                'fields' => [
                    self::field('paid_hours_per_day', 'Paid hours per day', 'hours', 'Fallback when an employee has no Work Schedule. Hourly rate = daily rate ÷ this.', [
                        'payroll.attendance.paid_hours_per_day' => 'value',
                        'payroll.hours_per_day' => 'value',
                        'payroll.salary_rate.paid_hours_per_day' => 'value',
                        'payroll.attendance.paid_minutes_per_day' => 'minutes',
                        'payroll.minutes_per_day' => 'minutes',
                    ], min: 1, max: 24, step: 0.5),
                    self::field('scheduled_hours_per_day', 'Scheduled clock hours per day', 'hours', 'Clock time from time-in to time-out, including the unpaid break.', [
                        'payroll.attendance.scheduled_hours_per_day' => 'value',
                        'payroll.scheduled_hours_per_day' => 'value',
                        'payroll.attendance.scheduled_minutes_per_day' => 'minutes',
                        'payroll.scheduled_minutes_per_day' => 'minutes',
                    ], min: 1, max: 24, step: 0.5),
                    self::field('unpaid_break_minutes', 'Unpaid break', 'minutes', 'Lunch break that is not paid.', ['payroll.attendance.unpaid_break_minutes' => 'value'], min: 0, max: 240),
                    self::field('unpaid_break_start', 'Break starts', 'time', null, ['payroll.attendance.unpaid_break_start' => 'value']),
                    self::field('unpaid_break_end', 'Break ends', 'time', null, ['payroll.attendance.unpaid_break_end' => 'value']),
                    self::field('late_grace_minutes', 'Late grace period', 'minutes', 'Minutes after the scheduled time-in that are not counted as late.', [
                        'payroll.attendance.late_grace_minutes' => 'value',
                        'payroll.late_grace_minutes' => 'value',
                    ], min: 0, max: 120),
                    self::field('late_deduction_block_minutes', 'Late rounding block', 'minutes', 'Late minutes past the grace are rounded up to blocks of this size.', ['payroll.attendance.late_deduction_block_minutes' => 'value'], min: 1, max: 120),
                    self::field('undertime_grace_minutes', 'Undertime grace period', 'minutes', 'Leaving this many minutes early is not counted.', [
                        'payroll.attendance.undertime_grace_minutes' => 'value',
                        'payroll.undertime_grace_minutes' => 'value',
                    ], min: 0, max: 120),
                    self::field('undertime_deduction_block_minutes', 'Undertime rounding block', 'minutes', 'Undertime past the grace is rounded up to blocks of this size.', [
                        'payroll.attendance.undertime_deduction_block_minutes' => 'value',
                        'payroll.undertime_deduction_block_minutes' => 'value',
                    ], min: 1, max: 120),
                    self::field('duplicate_punch_window_minutes', 'Duplicate scan window', 'minutes', 'Punches this close to the first time-in are treated as a double scan, not a time-out.', ['payroll.attendance.duplicate_punch_window_minutes' => 'value'], min: 0, max: 240),
                    self::field('rest_day_minimum_valid_log_days', 'Rest day: minimum valid log days', 'days', 'Valid time-in + time-out days a cutoff needs to keep the unworked day off paid.', ['payroll.attendance.rest_day_minimum_valid_log_days' => 'value'], min: 0, max: 15),
                    self::field('rest_day_adjustment_or_leave_exception', 'Rest day: adjustment or leave keeps it paid', 'select', 'An approved adjustment or leave in the cutoff keeps the day off paid even with fewer log days.', ['payroll.attendance.rest_day_adjustment_or_leave_exception' => 'map'], options: ['yes' => 'Yes', 'no' => 'No'], map: ['yes' => true, 'no' => false]),
                ],
            ],
            [
                'key' => 'salary',
                'title' => 'Salary rates',
                'description' => 'How daily, hourly and cutoff pay are derived from the salary in Employee Rates.',
                'fields' => [
                    self::field('annual_months', 'Months per year', 'number', 'Daily rate = monthly salary × months ÷ days per year.', ['payroll.salary_rate.annual_months' => 'value'], min: 1, max: 24, step: 1),
                    self::field('annual_days', 'Days per year', 'number', 'Use 365, or e.g. 313 / 261 for a factor-rate company.', ['payroll.salary_rate.annual_days' => 'value'], min: 100, max: 366, step: 1),
                    self::field('monthly_cutoff_divisor', 'Cutoffs per month', 'number', 'A monthly employee gets monthly salary ÷ this each cutoff.', ['payroll.salary_rate.monthly_cutoff_divisor' => 'value'], min: 1, max: 4, step: 1),
                ],
            ],
            [
                'key' => 'premiums',
                'title' => 'Overtime & night differential',
                'description' => 'Rates applied to approved overtime and to work between the night hours.',
                'fields' => [
                    self::field('premium_base_hours', 'Hours per day for OT / holiday rate', 'hours', 'OT and holiday hourly base = daily rate ÷ this (a 9-hour schedule does not dilute OT).', ['payroll.premiums.standard_daily_hours' => 'value'], min: 1, max: 24, step: 0.5),
                    self::field('overtime_regular', 'Overtime on an ordinary day', 'percent', 'Of the hourly rate.', [
                        'payroll.premiums.overtime_multiplier' => 'value',
                        'payroll.overtime.regular_multiplier' => 'value',
                    ], min: 1, max: 10, step: 0.0001),
                    self::field('overtime_premium_day', 'Overtime on a rest day / holiday', 'percent', 'Applied on top of that day\'s rate.', ['payroll.premiums.premium_day_overtime_multiplier' => 'value'], min: 1, max: 10, step: 0.0001),
                    self::field('night_differential_percent', 'Night differential', 'percent', 'Extra on top of the hourly rate for night hours.', ['payroll.premiums.night_differential_percent' => 'value'], min: 0, max: 1, step: 0.0001),
                    self::field('night_start', 'Night hours start', 'time', null, ['payroll.premiums.night_start' => 'value']),
                    self::field('night_end', 'Night hours end', 'time', null, ['payroll.premiums.night_end' => 'value']),
                ],
            ],
            [
                'key' => 'holidays',
                'title' => 'Holidays & rest days',
                'description' => 'Total rate of the day. Each holiday in Holiday Calendar starts with these and can be set higher there.',
                'fields' => [
                    self::field('rest_day_worked', 'Rest day worked', 'percent', null, ['payroll.holiday.rest_day_worked_multiplier' => 'value'], min: 1, max: 10, step: 0.0001),
                    self::field('regular_holiday_worked', 'Regular holiday worked', 'percent', null, ['payroll.holiday.regular_worked_multiplier' => 'value'], min: 1, max: 10, step: 0.0001),
                    self::field('regular_holiday_not_worked', 'Regular holiday not worked', 'percent', 'Daily-paid employees who qualify.', ['payroll.holiday.regular_not_worked_multiplier' => 'value'], min: 0, max: 10, step: 0.0001),
                    self::field('regular_holiday_rest_day', 'Regular holiday on a rest day, worked', 'percent', null, ['payroll.day_multipliers.regular_holiday_rest_day' => 'value'], min: 1, max: 10, step: 0.0001),
                    self::field('special_holiday_worked', 'Special holiday worked', 'percent', null, ['payroll.holiday.special_worked_multiplier' => 'value'], min: 1, max: 10, step: 0.0001),
                    self::field('special_holiday_not_worked', 'Special holiday not worked', 'percent', '0% = no work, no pay.', ['payroll.holiday.special_not_worked_multiplier' => 'value'], min: 0, max: 10, step: 0.0001),
                    self::field('special_holiday_rest_day', 'Special holiday on a rest day, worked', 'percent', null, ['payroll.day_multipliers.special_holiday_rest_day' => 'value'], min: 1, max: 10, step: 0.0001),
                    self::field('holiday_qualification', 'Unworked regular holiday is paid when', 'select', null, [
                        'payroll.holiday_requires_before_work_only' => 'map',
                        'payroll.holiday_requires_before_after_work' => 'map',
                    ], options: self::HOLIDAY_QUALIFICATION_OPTIONS, map: [
                        'day_before' => ['payroll.holiday_requires_before_work_only' => true, 'payroll.holiday_requires_before_after_work' => false],
                        'day_before_and_after' => ['payroll.holiday_requires_before_work_only' => false, 'payroll.holiday_requires_before_after_work' => true],
                        'none' => ['payroll.holiday_requires_before_work_only' => false, 'payroll.holiday_requires_before_after_work' => false],
                    ]),
                ],
            ],
            [
                'key' => 'sss',
                'title' => 'SSS',
                'description' => 'Monthly Salary Credit (MSC) brackets and shares. MSC above the regular cap goes to the MPF.',
                'fields' => [
                    self::field('sss_basis', 'Computed from', 'select', null, ['payroll.government_basis.sss' => 'value'], options: self::BASIS_OPTIONS),
                    self::field('sss_schedule', 'Deduct on', 'select', 'Employee Rates can still override this per employee.', ['payroll.government_deduction_schedule.sss' => 'value'], options: self::SCHEDULE_OPTIONS),
                    self::field('sss_employee_rate', 'Employee share', 'percent', 'Of the MSC.', ['sss.business_employee.employee_rate' => 'value', 'sss.business_employee.total_rate' => 'sum'], min: 0, max: 1, step: 0.0001),
                    self::field('sss_employer_rate', 'Employer share', 'percent', 'Of the MSC.', ['sss.business_employee.employer_rate' => 'value', 'sss.business_employee.total_rate' => 'sum'], min: 0, max: 1, step: 0.0001),
                    self::field('sss_minimum_msc', 'Lowest MSC', 'money', null, ['sss.business_employee.minimum_msc' => 'value'], min: 0, step: 0.01),
                    self::field('sss_maximum_msc', 'Highest MSC', 'money', null, ['sss.business_employee.maximum_msc' => 'value'], min: 0, step: 0.01),
                    self::field('sss_msc_increment', 'MSC step', 'money', 'Each bracket is this much wider than the last.', ['sss.business_employee.msc_increment' => 'value'], min: 1, step: 0.01),
                    self::field('sss_first_middle_range', 'Pay below this uses the lowest MSC', 'money', null, ['sss.business_employee.first_middle_range' => 'value'], min: 0, step: 0.01),
                    self::field('sss_maximum_range_start', 'Pay from this uses the highest MSC', 'money', null, ['sss.business_employee.maximum_range_start' => 'value'], min: 0, step: 0.01),
                    self::field('sss_regular_ss_msc_cap', 'Regular SS cap (rest goes to MPF)', 'money', null, ['sss.business_employee.regular_ss_msc_cap' => 'value'], min: 0, step: 0.01),
                    self::field('sss_ec_low_amount', 'EC (employer) up to the EC limit', 'money', null, ['sss.business_employee.ec_low_amount' => 'value'], min: 0, step: 0.01),
                    self::field('sss_ec_high_amount', 'EC (employer) above the EC limit', 'money', null, ['sss.business_employee.ec_high_amount' => 'value'], min: 0, step: 0.01),
                    self::field('sss_ec_low_msc_maximum', 'EC limit (MSC)', 'money', null, ['sss.business_employee.ec_low_msc_maximum' => 'value'], min: 0, step: 0.01),
                    self::field('sss_circular_number', 'SSS circular', 'text', 'Reference shown on payroll details.', ['sss.business_employee.circular_number' => 'value']),
                    self::field('sss_circular_effective', 'Circular effective from', 'date', null, ['sss.business_employee.effective_from' => 'value']),
                ],
            ],
            [
                'key' => 'philhealth',
                'title' => 'PhilHealth',
                'description' => 'Premium = salary (kept between the floor and ceiling) × rate, split between employee and employer.',
                'fields' => [
                    self::field('philhealth_basis', 'Computed from', 'select', null, ['payroll.government_basis.philhealth' => 'value'], options: self::BASIS_OPTIONS),
                    self::field('philhealth_schedule', 'Deduct on', 'select', 'Employee Rates can still override this per employee.', ['payroll.government_deduction_schedule.philhealth' => 'value'], options: self::SCHEDULE_OPTIONS),
                    self::field('philhealth_rate', 'Premium rate', 'percent', null, ['payroll.government.philhealth.premium_rate' => 'value'], min: 0, max: 1, step: 0.0001),
                    self::field('philhealth_employee_share', 'Employee pays', 'percent', 'Of the premium. The employer pays the rest.', ['payroll.government.philhealth.employee_share' => 'value'], min: 0, max: 1, step: 0.0001),
                    self::field('philhealth_floor', 'Salary floor', 'money', null, ['payroll.government.philhealth.income_floor' => 'value'], min: 0, step: 0.01),
                    self::field('philhealth_ceiling', 'Salary ceiling', 'money', null, ['payroll.government.philhealth.income_ceiling' => 'value'], min: 0, step: 0.01),
                ],
            ],
            [
                'key' => 'pagibig',
                'title' => 'Pag-IBIG',
                'description' => 'Contribution = fund salary (capped) × rate.',
                'fields' => [
                    self::field('pagibig_basis', 'Computed from', 'select', null, ['payroll.government_basis.pagibig' => 'value'], options: self::BASIS_OPTIONS),
                    self::field('pagibig_schedule', 'Deduct on', 'select', 'Employee Rates can still override this per employee.', ['payroll.government_deduction_schedule.pagibig' => 'value'], options: self::SCHEDULE_OPTIONS),
                    self::field('pagibig_regular_employee_rate', 'Employee rate', 'percent', null, ['payroll.government.pagibig.regular_employee_rate' => 'value'], min: 0, max: 1, step: 0.0001),
                    self::field('pagibig_low_employee_rate', 'Employee rate for low salary', 'percent', null, ['payroll.government.pagibig.low_employee_rate' => 'value'], min: 0, max: 1, step: 0.0001),
                    self::field('pagibig_low_salary_threshold', 'Low salary up to', 'money', null, ['payroll.government.pagibig.low_salary_threshold' => 'value'], min: 0, step: 0.01),
                    self::field('pagibig_employer_rate', 'Employer rate', 'percent', null, ['payroll.government.pagibig.employer_rate' => 'value'], min: 0, max: 1, step: 0.0001),
                    self::field('pagibig_max_fund_salary', 'Maximum fund salary', 'money', null, ['payroll.government.pagibig.maximum_fund_salary' => 'value'], min: 0, step: 0.01),
                ],
            ],
        ];
    }

    /** @return array<string, array<string, mixed>> key => field */
    public static function fields(): array
    {
        if (self::$fields === null) {
            self::$fields = [];
            foreach (self::sections() as $section) {
                foreach ($section['fields'] as $field) {
                    self::$fields[$field['key']] = $field + ['section' => $section['key']];
                }
            }
        }

        return self::$fields;
    }

    /**
     * Values of the shipped config files (the starting point of every setting).
     *
     * @return array<string, mixed> key => value
     */
    public static function defaults(): array
    {
        $values = [];

        foreach (self::fields() as $key => $field) {
            $values[$key] = self::defaultValue($field);
        }

        return $values;
    }

    /**
     * Fill missing keys from the defaults and cast every value to its type.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public static function normalize(array $values): array
    {
        $defaults = self::defaults();
        $normalized = [];

        foreach (self::fields() as $key => $field) {
            $normalized[$key] = self::cast($field, array_key_exists($key, $values) ? $values[$key] : $defaults[$key]);
        }

        return $normalized;
    }

    /**
     * Turn settings values into config path => value.
     *
     * @param  array<string, mixed>  $values  normalized values
     * @return array<string, mixed>
     */
    public static function toConfig(array $values): array
    {
        $config = [];

        foreach (self::fields() as $key => $field) {
            $value = $values[$key];

            foreach ($field['targets'] as $path => $transform) {
                $config[$path] = match ($transform) {
                    'minutes' => (int) round((float) $value * 60),
                    'map' => is_array($field['map'][$value] ?? null) ? $field['map'][$value][$path] : ($field['map'][$value] ?? null),
                    'sum' => round((float) ($config[$path] ?? 0) + (float) $value, 6),
                    default => $value,
                };
            }
        }

        return $config;
    }

    /**
     * Sections for the React form (no config paths).
     *
     * @return list<array{key: string, title: string, description: string, fields: list<array<string, mixed>>}>
     */
    public static function publicSections(): array
    {
        return array_map(fn (array $section): array => [
            ...$section,
            'fields' => array_map(fn (array $field): array => array_diff_key($field, ['targets' => true, 'map' => true]), $section['fields']),
        ], self::sections());
    }

    /** A value as people read it: "125%", "₱10,000.00", "8 hr", "15 min". */
    public static function display(string $key, mixed $value): string
    {
        $field = self::fields()[$key] ?? null;

        if ($field === null) {
            return (string) $value;
        }

        $number = fn (float $amount, int $decimals = 4): string => rtrim(rtrim(number_format($amount, $decimals, '.', ','), '0'), '.');

        return match ($field['type']) {
            'percent' => $number((float) $value * 100).'%',
            'money' => '₱'.number_format((float) $value, 2),
            'hours' => $number((float) $value, 2).' hr',
            'minutes' => (int) $value.' min',
            'days' => (int) $value.' day(s)',
            'select' => (string) ($field['options'][(string) $value] ?? $value),
            'number' => $number((float) $value),
            default => (string) $value,
        };
    }

    /**
     * Fields whose value differs between two value sets.
     *
     * @param  array<string, mixed>  $from
     * @param  array<string, mixed>  $to
     * @return list<array{key: string, label: string, from: string, to: string}>
     */
    public static function changes(array $from, array $to): array
    {
        $from = self::normalize($from);
        $to = self::normalize($to);
        $changes = [];

        foreach (self::fields() as $key => $field) {
            $same = is_numeric($from[$key]) && is_numeric($to[$key])
                ? abs((float) $from[$key] - (float) $to[$key]) < 0.0000001
                : (string) $from[$key] === (string) $to[$key];

            if (! $same) {
                $changes[] = ['key' => $key, 'label' => $field['label'], 'from' => self::display($key, $from[$key]), 'to' => self::display($key, $to[$key])];
            }
        }

        return $changes;
    }

    /**
     * Laravel validation rules for a `values` array.
     *
     * @return array<string, list<mixed>>
     */
    public static function rules(string $prefix = 'values'): array
    {
        $rules = [$prefix => ['required', 'array']];

        foreach (self::fields() as $key => $field) {
            $rule = ['required'];

            $rule = match ($field['type']) {
                'select' => [...$rule, 'in:'.implode(',', array_keys($field['options']))],
                'time' => [...$rule, 'date_format:H:i'],
                'date' => [...$rule, 'date_format:Y-m-d'],
                'text' => [...$rule, 'string', 'max:100'],
                default => [...$rule, 'numeric', ...(isset($field['min']) ? ['min:'.$field['min']] : []), ...(isset($field['max']) ? ['max:'.$field['max']] : [])],
            };

            $rules[$prefix.'.'.$key] = $rule;
        }

        return $rules;
    }

    /** @return array<string, string> validation attribute names */
    public static function attributes(string $prefix = 'values'): array
    {
        $names = [];

        foreach (self::fields() as $key => $field) {
            $names[$prefix.'.'.$key] = $field['label'];
        }

        return $names;
    }

    /**
     * @param  array<string, string>|null  $options
     * @param  array<string, mixed>|null  $map
     * @param  array<string, string>  $targets
     * @return array<string, mixed>
     */
    private static function field(
        string $key,
        string $label,
        string $type,
        ?string $help,
        array $targets,
        ?float $min = null,
        ?float $max = null,
        ?float $step = null,
        ?array $options = null,
        ?array $map = null,
    ): array {
        return array_filter([
            'key' => $key,
            'label' => $label,
            'type' => $type,
            'help' => $help,
            'targets' => $targets,
            'min' => $min,
            'max' => $max,
            'step' => $step,
            'options' => $options,
            'map' => $map,
        ], fn ($value): bool => $value !== null);
    }

    /** @param  array<string, mixed>  $field */
    private static function defaultValue(array $field): mixed
    {
        $paths = array_keys($field['targets']);

        if ($field['type'] === 'select' && isset($field['map'])) {
            foreach ($field['map'] as $option => $mapped) {
                $expected = is_array($mapped) ? $mapped : [$paths[0] => $mapped];
                $matches = collect($expected)->every(fn ($value, string $path): bool => (bool) self::factoryValue($path) === (bool) $value);

                if ($matches) {
                    return $option;
                }
            }

            return array_key_first($field['map']);
        }

        return self::cast($field, self::factoryValue($paths[0]));
    }

    /** Value of a config path in the shipped config file, not the runtime config. */
    private static function factoryValue(string $path): mixed
    {
        [$file, $rest] = explode('.', $path, 2);

        if (! isset(self::$factory[$file])) {
            $loaded = is_file(config_path($file.'.php')) ? require config_path($file.'.php') : [];
            self::$factory[$file] = is_array($loaded) ? $loaded : [];
        }

        return data_get(self::$factory[$file], $rest, self::fallback($path));
    }

    /** Values for keys the engine reads with a default but the config file does not list. */
    private static function fallback(string $path): mixed
    {
        return [
            'payroll.attendance.duplicate_punch_window_minutes' => 30,
            'payroll.day_multipliers.regular_holiday_rest_day' => 2.60,
            'payroll.day_multipliers.special_holiday_rest_day' => 1.50,
            'payroll.government.philhealth.premium_rate' => 0.05,
            'payroll.government.philhealth.employee_share' => 0.50,
            'payroll.government.philhealth.income_floor' => 10000.00,
            'payroll.government.philhealth.income_ceiling' => 100000.00,
            'payroll.government.pagibig.low_employee_rate' => 0.01,
            'payroll.government.pagibig.regular_employee_rate' => 0.02,
            'payroll.government.pagibig.employer_rate' => 0.02,
            'payroll.government.pagibig.low_salary_threshold' => 1500.00,
            'payroll.government.pagibig.maximum_fund_salary' => 10000.00,
        ][$path] ?? null;
    }

    /** @param  array<string, mixed>  $field */
    private static function cast(array $field, mixed $value): mixed
    {
        return match ($field['type']) {
            'select' => array_key_exists((string) $value, $field['options']) ? (string) $value : (string) array_key_first($field['options']),
            'time' => substr(trim((string) $value), 0, 5),
            'text', 'date' => trim((string) $value),
            'minutes', 'days' => (int) round((float) $value),
            default => round((float) $value, 6),
        };
    }
}
