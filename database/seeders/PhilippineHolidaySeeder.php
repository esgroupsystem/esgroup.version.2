<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class PhilippineHolidaySeeder extends Seeder
{
    public function run(): void
    {
        $specialNotWorked = 1.00; // YOUR COMPANY RULE
        // legal/default PH usually = 0.00

        $holidays = [
            // REGULAR HOLIDAYS - 2026
            [
                'name' => "New Year's Day",
                'actual_date' => '2026-01-01',
                'observed_date' => '2026-01-01',
                'holiday_type' => 'regular',
                'is_moved' => false,
                'not_worked_multiplier' => 1.00,
                'worked_multiplier' => 2.00,
                'source_proclamation' => 'Proclamation No. 1006, s. 2025',
            ],
            [
                'name' => "Eid'l Fitr",
                'actual_date' => '2026-03-20',
                'observed_date' => '2026-03-20',
                'holiday_type' => 'regular',
                'is_moved' => false,
                'not_worked_multiplier' => 1.00,
                'worked_multiplier' => 2.00,
                'source_proclamation' => 'Proclamation No. 1189, s. 2026',
            ],
            [
                'name' => 'Maundy Thursday',
                'actual_date' => '2026-04-02',
                'observed_date' => '2026-04-02',
                'holiday_type' => 'regular',
                'is_moved' => false,
                'not_worked_multiplier' => 1.00,
                'worked_multiplier' => 2.00,
                'source_proclamation' => 'Proclamation No. 1006, s. 2025',
            ],
            [
                'name' => 'Good Friday',
                'actual_date' => '2026-04-03',
                'observed_date' => '2026-04-03',
                'holiday_type' => 'regular',
                'is_moved' => false,
                'not_worked_multiplier' => 1.00,
                'worked_multiplier' => 2.00,
                'source_proclamation' => 'Proclamation No. 1006, s. 2025',
            ],
            [
                'name' => 'Araw ng Kagitingan',
                'actual_date' => '2026-04-09',
                'observed_date' => '2026-04-09',
                'holiday_type' => 'regular',
                'is_moved' => false,
                'not_worked_multiplier' => 1.00,
                'worked_multiplier' => 2.00,
                'source_proclamation' => 'Proclamation No. 1006, s. 2025',
            ],
            [
                'name' => 'Labor Day',
                'actual_date' => '2026-05-01',
                'observed_date' => '2026-05-01',
                'holiday_type' => 'regular',
                'is_moved' => false,
                'not_worked_multiplier' => 1.00,
                'worked_multiplier' => 2.00,
                'source_proclamation' => 'Proclamation No. 1006, s. 2025',
            ],
            [
                'name' => 'Independence Day',
                'actual_date' => '2026-06-12',
                'observed_date' => '2026-06-12',
                'holiday_type' => 'regular',
                'is_moved' => false,
                'not_worked_multiplier' => 1.00,
                'worked_multiplier' => 2.00,
                'source_proclamation' => 'Proclamation No. 1006, s. 2025',
            ],
            [
                'name' => 'National Heroes Day',
                'actual_date' => '2026-08-31',
                'observed_date' => '2026-08-31',
                'holiday_type' => 'regular',
                'is_moved' => false,
                'not_worked_multiplier' => 1.00,
                'worked_multiplier' => 2.00,
                'source_proclamation' => 'Proclamation No. 1006, s. 2025',
            ],
            [
                'name' => 'Bonifacio Day',
                'actual_date' => '2026-11-30',
                'observed_date' => '2026-11-30',
                'holiday_type' => 'regular',
                'is_moved' => false,
                'not_worked_multiplier' => 1.00,
                'worked_multiplier' => 2.00,
                'source_proclamation' => 'Proclamation No. 1006, s. 2025',
            ],
            [
                'name' => 'Christmas Day',
                'actual_date' => '2026-12-25',
                'observed_date' => '2026-12-25',
                'holiday_type' => 'regular',
                'is_moved' => false,
                'not_worked_multiplier' => 1.00,
                'worked_multiplier' => 2.00,
                'source_proclamation' => 'Proclamation No. 1006, s. 2025',
            ],
            [
                'name' => 'Rizal Day',
                'actual_date' => '2026-12-30',
                'observed_date' => '2026-12-30',
                'holiday_type' => 'regular',
                'is_moved' => false,
                'not_worked_multiplier' => 1.00,
                'worked_multiplier' => 2.00,
                'source_proclamation' => 'Proclamation No. 1006, s. 2025',
            ],

            // SPECIAL NON-WORKING DAYS - 2026
            [
                'name' => 'Chinese New Year',
                'actual_date' => '2026-02-17',
                'observed_date' => '2026-02-17',
                'holiday_type' => 'special',
                'is_moved' => false,
                'not_worked_multiplier' => $specialNotWorked,
                'worked_multiplier' => 1.30,
                'source_proclamation' => 'Proclamation No. 1006, s. 2025',
            ],
            [
                'name' => 'Black Saturday',
                'actual_date' => '2026-04-04',
                'observed_date' => '2026-04-04',
                'holiday_type' => 'special',
                'is_moved' => false,
                'not_worked_multiplier' => $specialNotWorked,
                'worked_multiplier' => 1.30,
                'source_proclamation' => 'Proclamation No. 1006, s. 2025',
            ],
            [
                'name' => 'Ninoy Aquino Day',
                'actual_date' => '2026-08-21',
                'observed_date' => '2026-08-21',
                'holiday_type' => 'special',
                'is_moved' => false,
                'not_worked_multiplier' => $specialNotWorked,
                'worked_multiplier' => 1.30,
                'source_proclamation' => 'Proclamation No. 1006, s. 2025',
            ],
            [
                'name' => "All Saints' Day Eve",
                'actual_date' => '2026-10-31',
                'observed_date' => '2026-10-31',
                'holiday_type' => 'special',
                'is_moved' => false,
                'not_worked_multiplier' => $specialNotWorked,
                'worked_multiplier' => 1.30,
                'source_proclamation' => 'Proclamation No. 1006, s. 2025',
            ],
            [
                'name' => "All Saints' Day",
                'actual_date' => '2026-11-01',
                'observed_date' => '2026-11-01',
                'holiday_type' => 'special',
                'is_moved' => false,
                'not_worked_multiplier' => $specialNotWorked,
                'worked_multiplier' => 1.30,
                'source_proclamation' => 'Proclamation No. 1006, s. 2025',
            ],
            [
                'name' => 'Feast of the Immaculate Conception of Mary',
                'actual_date' => '2026-12-08',
                'observed_date' => '2026-12-08',
                'holiday_type' => 'special',
                'is_moved' => false,
                'not_worked_multiplier' => $specialNotWorked,
                'worked_multiplier' => 1.30,
                'source_proclamation' => 'Proclamation No. 1006, s. 2025',
            ],
            [
                'name' => 'Last Day of the Year',
                'actual_date' => '2026-12-31',
                'observed_date' => '2026-12-31',
                'holiday_type' => 'special',
                'is_moved' => false,
                'not_worked_multiplier' => $specialNotWorked,
                'worked_multiplier' => 1.30,
                'source_proclamation' => 'Proclamation No. 1006, s. 2025',
            ],
        ];

        foreach ($holidays as $holiday) {
            Holiday::updateOrCreate(
                [
                    'name' => $holiday['name'],
                    'observed_date' => $holiday['observed_date'],
                ],
                $holiday
            );
        }
    }
}
