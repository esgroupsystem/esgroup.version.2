<?php

declare(strict_types=1);

namespace App\Repositories\Biometrics;

use App\Models\BiometricCompany;
use App\Repositories\Contracts\Biometrics\BiometricCompanyRepositoryInterface;
use Illuminate\Support\Collection;

final class BiometricCompanyRepository implements BiometricCompanyRepositoryInterface
{
    public function all(): Collection
    {
        return BiometricCompany::query()->orderBy('name')->get();
    }
}
