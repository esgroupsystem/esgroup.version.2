<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payroll Cutoff Display Tags
    |--------------------------------------------------------------------------
    |
    | IMPORTANT: The database values `first` and `second` are legacy internal
    | keys and are intentionally NOT changed by this update. Only the user-facing
    | business tags are changed:
    |
    | - Internal `second` = 26th to 10th = BUSINESS 1ST CUTOFF
    | - Internal `first`  = 11th to 25th = BUSINESS 2ND CUTOFF
    |
    | Keeping the stored keys unchanged prevents any change to attendance,
    | payroll computation, government deductions, historical records, and APIs.
    |
    */
    'cutoff_display' => [
        'first' => [
            'label' => '2nd Cutoff',
            'range' => '11-25',
            'full' => '2nd Cutoff (11-25)',
        ],
        'second' => [
            'label' => '1st Cutoff',
            'range' => '26-10',
            'full' => '1st Cutoff (26-10)',
        ],
    ],

    'cutoff_display_by_range' => [
        '11_25' => '2nd Cutoff (11-25)',
        '26_10' => '1st Cutoff (26-10)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Attendance payroll rules
    |--------------------------------------------------------------------------
    |
    | Default / legacy standard:
    | - 8 paid work hours + 1 unpaid lunch hour = 9 clock hours.
    | - Permanent Work Schedule may override this per employee with either:
    |   8 paid hours + 1 lunch hour, or 9 paid hours + 1 lunch hour.
    | - These values remain the safe fallback for legacy attendance rows.
    |
    */

    'attendance' => [
        'scheduled_hours_per_day' => 9,
        'scheduled_minutes_per_day' => 540,
        // Fallback only. Permanent schedule may use 8 or 9 paid hours.
        'paid_hours_per_day' => 8,
        'paid_minutes_per_day' => 480,

        /*
         | Worked-time display stores paid work minutes, not raw clock span.
         | Example: 7:00 AM to 4:00 PM = 540 clock minutes - 60 lunch minutes
         | = 480 worked minutes / 8.00 worked hours.
         */
        'unpaid_break_minutes' => 60,
        'unpaid_break_start' => '12:00',
        'unpaid_break_end' => '13:00',

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
    | hourly_rate = daily_rate / employee_schedule_paid_hours
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
    |
    | SSS uses the employee's actual GROSS compensation for the contribution
    | cycle (business 1st cutoff: 26-10 + business 2nd cutoff: 11-25), then
    | applies the official MSC bracket. This includes recurring allowances/additions that
    | form part of remuneration. PhilHealth uses fixed monthly basic salary and
    | must not be reduced
    | by absence, tardiness, undertime, or leave-without-pay deductions.
    | Pag-IBIG uses fixed monthly basic salary with a P10,000 maximum fund salary.
    |
    */

    'government_basis' => [
        'sss' => 'actual_cycle_basic',
        'philhealth' => 'fixed_monthly_basic',
        'pagibig' => 'fixed_monthly_basic',
    ],

    /*
    | Deduct the complete monthly statutory contribution when both halves of the
    | monthly cycle are available. The legacy internal key `first_cutoff` means
    | 11-25, which is the BUSINESS 2ND CUTOFF under the display convention above.
    | Do not rename these stored schedule keys without a dedicated data migration.
    */
    'government_deduction_schedule' => [
        'sss' => 'first_cutoff',
        'philhealth' => 'first_cutoff',
        'pagibig' => 'first_cutoff',
    ],
];
