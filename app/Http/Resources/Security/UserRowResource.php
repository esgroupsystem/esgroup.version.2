<?php

declare(strict_types=1);

namespace App\Http\Resources\Security;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One row of Security → Users (`authentication/users/index`).
 *
 * @mixin User
 */
final class UserRowResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role ?? 'N/A',
            'role_name' => $this->roles->pluck('name')->first() ?? (string) ($this->role ?? ''),
            'location_id' => $this->location_id ? (string) $this->location_id : '',
            'account_status' => $this->account_status,
            'last_online' => $this->last_online?->format('M d, Y h:i A'),
            'updated_at' => $this->updated_at?->format('M d, Y h:i A'),
            'is_self' => $this->resource->is($request->user()),
            'update_url' => route('authentication.users.update', $this->id),
            'reset_url' => route('authentication.users.reset.password', $this->id),
            'status_url' => route('authentication.users.status', $this->id),
        ];
    }
}
