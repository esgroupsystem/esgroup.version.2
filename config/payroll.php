<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Attendance payroll rules
    |--------------------------------------------------------------------------
    |
    | Company standard:
    | - Employee stays in the workplace for 9 clock hours.
    | - Only 8 hours are paid work hours because 1 hour is lunch.
    | - Payroll rates use 8 paid hours.
    | - Attendance schedule validation still uses 9 clock hours.
    |
    */

    'attendance' => [
        'scheduled_hours_per_day' => 9,
        'scheduled_minutes_per_day' => 540,
        'paid_hours_per_day' => 8,
        'paid_minutes_per_day' => 480,

        'late_grace_minutes' => 15,
        'late_deduction_block_minutes' => 30,

        /*
         | Undertime rule:
         | Schedule 7:00 AM - 4:00 PM
         | 3:14 PM out = 46 minutes raw undertime = 60 minutes deduction
         | 3:44 PM out = 16 minutes raw undertime = 30 minutes deduction
         | 3:55 PM out = 5 minutes raw undertime = 0 minutes deduction
         */
        'undertime_grace_minutes' => 5,
        'undertime_deduction_block_minutes' => 30,
    ],

    /*
    | Old root keys retained for old views/services that still call config().
    */
    'late_grace_minutes' => 15,
    'undertime_grace_minutes' => 5,
    'undertime_deduction_block_minutes' => 30,
    'hours_per_day' => 8,
    'minutes_per_day' => 480,
    'scheduled_hours_per_day' => 9,
    'scheduled_minutes_per_day' => 540,

    /*
    |--------------------------------------------------------------------------
    | Salary Rate Standard
    |--------------------------------------------------------------------------
    |
    | Monthly salary basic cutoff pay is always monthly_salary / 2.
    | Do not compute monthly cutoff basic pay using daily_rate * days in month.
    |
    | Daily rate is used only for deductions and premiums:
    | daily_rate = monthly_salary * 12 / 365
    | hourly_rate = daily_rate / 8
    | minute_rate = hourly_rate / 60
    |
    */

    'salary_rate' => [
        'monthly_cutoff_divisor' => 2,
        'annual_months' => 12,
        'annual_days' => 365,
        'paid_hours_per_day' => 8,
        'minutes_per_hour' => 60,
    ],

    /*
    | Retained legacy keys. These are no longer used for monthly cutoff basic pay.
    */
    'monthly_working_days' => 26,
    'monthly_salary_divisor' => 26,
    'monthly_cutoff_paid_days' => 13,

    /*
    |--------------------------------------------------------------------------
    | Holiday Qualification Rule
    |--------------------------------------------------------------------------
    |
    | Company rule:
    | - Only check the day before the holiday.
    | - Employee must have work/time-in on the previous day, or previous day must
    |   be rest day/day off, holiday, or approved paid leave/adjustment.
    | - Holiday worked premium requires valid time-in/out and approved payroll
    |   attendance adjustment for that holiday date.
    |
    */

    'holiday_requires_before_work_only' => true,
    'holiday_requires_before_after_work' => false,

    'holiday' => [
        'regular_worked_premium' => 1.00,
        'special_worked_premium' => 0.30,
        'rest_day_worked_multiplier' => 1.30,

        /*
         | Legacy values retained for old screens/reports.
         */
        'regular_worked_multiplier' => 2.00,
        'regular_not_worked_multiplier' => 1.00,
        'special_worked_multiplier' => 1.30,
        'special_not_worked_multiplier' => 0.00,
    ],

    'overtime' => [
        'regular_multiplier' => 1.25,
    ],

    /*
    |--------------------------------------------------------------------------
    | Government Contribution Basis
    |--------------------------------------------------------------------------
    */

    'government_basis' => [
        'sss' => 'actual_cycle_basic',
        'philhealth' => 'actual_cycle_basic',
        'pagibig' => 'actual_cycle_basic',
    ],

    'government_deduction_schedule' => [
        'sss' => 'first_cutoff',
        'philhealth' => 'first_cutoff',
        'pagibig' => 'first_cutoff',
    ],
];
