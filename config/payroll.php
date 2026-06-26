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

    'monthly_working_days' => 26,

    /*
    |--------------------------------------------------------------------------
    | Holiday Qualification Rule
    |--------------------------------------------------------------------------
    |
    | Company rule:
    | Only the day BEFORE the holiday is checked.
    | The day AFTER the holiday is not required.
    |
    | Paid holiday if previous day is:
    | - worked / has time-in
    | - rest day / day off
    | - holiday
    | - approved leave
    |
    */

    'holiday_requires_before_work_only' => true,
    'holiday_requires_before_after_work' => false,

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
        'philhealth' => 'actual_cycle_basic',
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
        'sss' => 'first_cutoff',
        'philhealth' => 'first_cutoff',
        'pagibig' => 'first_cutoff',
    ],

    /*
    |--------------------------------------------------------------------------
    | Monthly Employee Salary Divisor
    |--------------------------------------------------------------------------
    |
    | Company rule:
    | Monthly salary is divided by 26 paid days.
    |
    | Example:
    | 25,000 / 26 = 961.5384615385
    |
    */

    'monthly_salary_divisor' => 26,
    'monthly_cutoff_paid_days' => 13,
];
