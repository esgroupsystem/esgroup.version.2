<?php

declare(strict_types=1);

namespace App\Actions\Api;

use App\Models\Fare;

final class CreateFareAction
{
    /** @param array<string, mixed> $data */
    public function execute(array $data): Fare
    {
        return Fare::create($data);
    }
}
