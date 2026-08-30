<?php

declare(strict_types=1);

namespace App\Actions\Api;

use App\Models\CashierRemittance;
use Illuminate\Contracts\Auth\Authenticatable;

final class CreateCashierRemittanceAction
{
    /** @param array<string, mixed> $data */
    public function execute(array $data, Authenticatable $user): CashierRemittance
    {
        $data['user_id'] = $user->getAuthIdentifier();
        $data['synced_at'] = now();

        return CashierRemittance::create($data);
    }
}
