<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Biometrics;

use App\Models\BiometricCompany;
use Illuminate\Support\Collection;

/** Company tags on biometric records. */
interface BiometricCompanyRepositoryInterface
{
    /** @return Collection<int, BiometricCompany> by name */
    public function all(): Collection;
}
