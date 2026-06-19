<?php

return [
    /*
     | Attendance payroll rules
     | 9 hours = 1 paid day. First 15 minutes late is free.
     */
    'attendance' => [
        'hours_per_day' => 9,
        'minutes_per_day' => 540,
        'late_grace_minutes' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Payroll Unit Standard
    |--------------------------------------------------------------------------
    |
    | One payroll day is 9 hours / 540 minutes.
    | Attendance summary uses this only for payable-day and audit conversion.
    | Monthly salary computation uses monthly salary / 2 per cutoff.
    |
    */

    'hours_per_day' => 9,
    'minutes_per_day' => 540,

    /*
    |--------------------------------------------------------------------------
    | Monthly Salary Divisor
    |--------------------------------------------------------------------------
    |
    | Used only to derive daily absence rate when the employee salary profile
    | has no explicit absent_deduction_per_day value.
    |
    | Example:
    | 19,000 / 22 = 863.64 per absent day.
    |
    */

    'monthly_working_days' => 22,

    /*
    |--------------------------------------------------------------------------
    | Holiday Rules
    |--------------------------------------------------------------------------
    */

    'holiday_requires_before_after_work' => true,

    'holiday' => [
        'regular_worked_multiplier' => 2.00,
        'regular_not_worked_multiplier' => 1.00,
        'special_worked_multiplier' => 1.30,
        'special_not_worked_multiplier' => 0.00,
        'rest_day_worked_multiplier' => 1.30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Government Contribution Basis
    |--------------------------------------------------------------------------
    |
    | actual_cycle_basic:
    |   Uses previous 2nd cutoff + current 1st cutoff cycle basis when applicable.
    |
    | fixed_monthly_basic:
    |   Uses the fixed monthly basic salary from employee salary profile.
    |
    */

    'government_basis' => [
        'sss' => 'actual_cycle_basic',
        'philhealth' => 'fixed_monthly_basic',
        'pagibig' => 'actual_cycle_basic',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Government Deduction Schedule
    |--------------------------------------------------------------------------
    |
    | Employee salary profile schedule overrides these defaults when the profile
    | has sss_deduction_schedule, pagibig_deduction_schedule, or
    | philhealth_deduction_schedule.
    |
    */

    'government_deduction_schedule' => [
        'sss' => 'first',
        'philhealth' => 'first',
        'pagibig' => 'second',
        'withholding_tax' => 'per_cutoff',
    ],
];
