<?php

declare(strict_types=1);

namespace App\Actions\Api;

use App\Models\Ticket;
use Illuminate\Contracts\Auth\Authenticatable;

final class CreateTicketAction
{
    /** @param array<string, mixed> $data */
    public function execute(array $data, Authenticatable $user): Ticket
    {
        return Ticket::create([
            ...$data,
            'user_id' => $user->getAuthIdentifier(),
        ]);
    }
}
