<?php

declare(strict_types=1);

namespace App\Actions\Api;

use App\Models\OdometerSubmission;
use Illuminate\Contracts\Auth\Authenticatable;

final class CreateOdometerSubmissionAction
{
    /** @param array<string, mixed> $data */
    public function execute(array $data, Authenticatable $user): OdometerSubmission
    {
        return OdometerSubmission::create([
            ...$data,
            'user_id' => $user->getAuthIdentifier(),
        ]);
    }
}
