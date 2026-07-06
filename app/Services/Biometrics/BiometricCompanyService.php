<?php

namespace App\Services\Biometrics;

use App\Models\BiometricCompany;
use Illuminate\Support\Facades\DB;

class BiometricCompanyService
{
    public function create(array $data): BiometricCompany
    {
        return DB::transaction(function () use ($data): BiometricCompany {
            return BiometricCompany::query()->create([
                'name' => trim((string) $data['name']),
                'remarks' => $data['remarks'] ?? null,
            ]);
        });
    }
}
