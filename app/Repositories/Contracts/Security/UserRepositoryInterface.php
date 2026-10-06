<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Security;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    /**
     * Users whose `role` column is one of the given roles, by full name.
     *
     * @param  list<string>  $roles
     * @return Collection<int, User>
     */
    public function withRoles(array $roles): Collection;

    /**
     * Same as withRoles(), with a `job_orders_assigned_count` attribute.
     *
     * @param  list<string>  $roles
     * @return Collection<int, User>
     */
    public function withRolesAndAssignedJobOrders(array $roles): Collection;

    /**
     * Users page: newest first, search on name, username, email and role. Developer accounts
     * are left out unless $includeDevelopers.
     *
     * @return LengthAwarePaginator<int, User>
     */
    public function paginate(string $search, bool $includeDevelopers, int $perPage = 10): LengthAwarePaginator;

    public function count(): int;

    public function findOrFail(int $id): User;

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): User;

    /** @param array<string, mixed> $attributes */
    public function update(User $user, array $attributes): void;

    /** Gives the user exactly this one role. */
    public function setRole(User $user, string $role): void;

    /** Signs the user out of the mobile app (deletes every API token). */
    public function revokeTokens(User $user): void;

    public function findByUsername(string $username): ?User;
}
