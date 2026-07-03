<?php

namespace App\Services\Biometrics;

use App\Models\BiometricCompany;

class BiometricCompanyService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): BiometricCompany
    {
        return BiometricCompany::query()->create([
            'name' => $data['name'],
            'is_active' => true,
            'remarks' => $data['remarks'] ?? null,
        ]);
    }
}
