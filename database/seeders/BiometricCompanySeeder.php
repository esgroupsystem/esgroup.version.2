<?php

namespace Database\Seeders;

use App\Models\BiometricCompany;
use Illuminate\Database\Seeder;

class BiometricCompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            'Jell Transport',
            'ES Transports',
            'Earthstar Transport',
            'Kellen Transport',
            'WENG',
            'ROVS',
        ];

        foreach ($companies as $company) {
            BiometricCompany::query()->firstOrCreate(
                ['name' => $company],
                ['is_active' => true]
            );
        }
    }
}
