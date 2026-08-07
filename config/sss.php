<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SSS Contribution Schedule for Business Employers and Employees
    |--------------------------------------------------------------------------
    |
    | Source: SSS Circular No. 2024-006, effective January 2025.
    |
    | Total contribution rate: 15%
    | - Employer share: 10%
    | - Employee share: 5%
    | - EC is paid entirely by the employer and is added separately.
    |
    | The Regular SS Monthly Salary Credit is capped at PHP 20,000. Any MSC
    | above PHP 20,000 is allocated to the Mandatory Provident Fund (MPF).
    |
    */

    'business_employee' => [
        'circular_number' => '2024-006',
        'effective_from' => '2025-01-01',

        'total_rate' => 0.15,
        'employee_rate' => 0.05,
        'employer_rate' => 0.10,

        'minimum_msc' => 5000.00,
        'maximum_msc' => 35000.00,
        'msc_increment' => 500.00,

        // Below PHP 5,250 uses MSC PHP 5,000.
        'first_middle_range' => 5250.00,

        // PHP 34,750 and above uses MSC PHP 35,000.
        'maximum_range_start' => 34750.00,

        'regular_ss_msc_cap' => 20000.00,

        'ec_low_amount' => 10.00,
        'ec_high_amount' => 30.00,
        'ec_low_msc_maximum' => 14500.00,
    ],
];
