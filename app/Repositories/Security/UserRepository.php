<?php

declare(strict_types=1);

namespace App\Repositories\Security;

use App\Models\User;
use App\Repositories\Contracts\Security\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class UserRepository implements UserRepositoryInterface
{
    public function withRoles(array $roles): Collection
    {
        return User::query()->whereIn('role', $roles)->orderBy('full_name')->get();
    }

    public function withRolesAndAssignedJobOrders(array $roles): Collection
    {
        return User::query()
            ->whereIn('role', $roles)
            ->withCount('jobOrdersAssigned')
            ->orderBy('full_name')
            ->get();
    }

    public function paginate(string $search, bool $includeDevelopers, int $perPage = 10): LengthAwarePaginator
    {
        return User::query()
            ->with('roles')
            ->when(! $includeDevelopers, fn (Builder $query) => $query->whereDoesntHave('roles', fn (Builder $role) => $role->where('name', 'Developer')))
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('full_name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhereHas('roles', fn (Builder $role) => $role->where('name', 'like', "%{$search}%"))))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function count(): int
    {
        return User::query()->count();
    }

    public function findOrFail(int $id): User
    {
        return User::query()->findOrFail($id);
    }

    public function create(array $attributes): User
    {
        return User::query()->create($attributes);
    }

    public function update(User $user, array $attributes): void
    {
        $user->update($attributes);
    }

    public function setRole(User $user, string $role): void
    {
        $user->syncRoles([$role]);
    }

    public function revokeTokens(User $user): void
    {
        $user->tokens()->delete();
    }
}
