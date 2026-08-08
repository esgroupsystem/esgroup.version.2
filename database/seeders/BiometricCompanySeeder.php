<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BiometricCompanySeeder extends Seeder
{
    /**
     * Seed the application's biometric companies.
     */
    public function run(): void
    {
        $companies = [
            'Jell Transport',
            'ES Transport',
            'Earthstar Transport',
            'Kellen Transport',
        ];

        $now = now();

        foreach ($companies as $companyName) {
            $existingCompany = DB::table('biometric_companies')
                ->where('name', $companyName)
                ->first();

            if ($existingCompany) {
                // Keep existing record but make sure it is active.
                DB::table('biometric_companies')
                    ->where('id', $existingCompany->id)
                    ->update([
                        'is_active' => true,
                        'updated_at' => $now,
                    ]);

                continue;
            }

            DB::table('biometric_companies')->insert([
                'name' => $companyName,
                'is_active' => true,
                'remarks' => 'Default biometric company',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
