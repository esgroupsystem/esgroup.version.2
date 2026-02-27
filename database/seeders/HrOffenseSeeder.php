<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HrOffense;
use Illuminate\Support\Facades\File;

class HrOffenseSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/hr_offenses.csv');

        if (!File::exists($path)) {
            return;
        }

        $file = fopen($path, 'r');

        $header = fgetcsv($file); // skip header row

        while ($row = fgetcsv($file)) {
            HrOffense::create([
                'section' => $row[0],
                'offense_description' => $row[1],
                'offense_type' => $row[2],
                'offense_gravity' => $row[3],
            ]);
        }

        fclose($file);
    }
}