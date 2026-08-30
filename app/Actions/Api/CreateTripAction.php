<?php

declare(strict_types=1);

namespace App\Actions\Api;

use App\Models\Trip;
use Illuminate\Contracts\Auth\Authenticatable;

final class CreateTripAction
{
    /** @param array<string, mixed> $data */
    public function execute(array $data, Authenticatable $user): Trip
    {
        return Trip::create([
            ...$data,
            'user_id' => $user->getAuthIdentifier(),
            'started_at' => now(),
        ]);
    }
}
