<?php

declare(strict_types=1);

namespace App\Services\Security;

use App\Models\Location;
use App\Models\User;
use App\Repositories\Contracts\Maintenance\LocationRepositoryInterface;
use App\Repositories\Contracts\Security\RoleRepositoryInterface;
use App\Repositories\Contracts\Security\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Security → Users. Only a Developer sees, manages or assigns the Developer role. New accounts and
 * password resets get a random temporary password (shown once) and must change it at next sign-in.
 * Deactivating an account or resetting its password signs it out of the mobile app.
 */
final class UserManagementService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly RoleRepositoryInterface $roles,
        private readonly LocationRepositoryInterface $locations,
    ) {}

    /** @return Collection<int, array{value: string, label: string}> active stockrooms a user can be tied to */
    public function locationOptions(): Collection
    {
        return $this->locations->active()
            ->map(fn (Location $location): array => ['value' => (string) $location->id, 'label' => (string) $location->name])
            ->values();
    }

    /** @return LengthAwarePaginator<int, User> */
    public function paginate(User $actor, string $search): LengthAwarePaginator
    {
        return $this->users->paginate(trim($search), $actor->isDeveloper());
    }

    /** @return Collection<int, string> role names the actor may assign, by name */
    public function assignableRoles(User $actor): Collection
    {
        return $this->roles->findByNames(User::availableAssignableRoles($actor))->pluck('name')->values();
    }

    /**
     * @param  array{full_name: string, username: string, email: string, role: string, location_id?: ?int}  $data
     * @return array{user: User, password: string}
     */
    public function create(User $actor, array $data): array
    {
        $this->assertRoleAssignmentAllowed($actor, $data['role']);
        $password = self::temporaryPassword();

        $user = DB::transaction(function () use ($data, $password): User {
            $user = $this->users->create([
                ...$this->profile($data),
                'password' => Hash::make($password),
                'account_status' => 'active',
                'status' => 'offline',
                'must_change_password' => true,
            ]);
            $this->users->setRole($user, $data['role']);

            return $user;
        });

        return ['user' => $user, 'password' => $password];
    }

    /**
     * @param  array{full_name: string, username: string, email: string, role: string, location_id?: ?int, account_status: string}  $data
     *
     * @throws ValidationException when the actor tries to deactivate their own account
     */
    public function update(User $actor, int $id, array $data): User
    {
        $user = $this->users->findOrFail($id);
        $this->assertTargetManageable($actor, $user);
        $this->assertRoleAssignmentAllowed($actor, $data['role']);
        if ($data['account_status'] !== 'active') {
            $this->assertNotSelf($actor, $user);
        }

        DB::transaction(function () use ($user, $data): void {
            $this->users->update($user, [...$this->profile($data), 'account_status' => $data['account_status']]);
            $this->users->setRole($user, $data['role']);

            if ($user->account_status !== 'active') {
                $this->users->revokeTokens($user);
            }
        });

        return $user;
    }

    /** @return array{user: User, password: string} */
    public function resetPassword(User $actor, int $id): array
    {
        $user = $this->users->findOrFail($id);
        $this->assertTargetManageable($actor, $user);
        $password = self::temporaryPassword();

        $this->users->update($user, ['password' => Hash::make($password), 'must_change_password' => true]);
        $this->users->revokeTokens($user);

        return ['user' => $user, 'password' => $password];
    }

    /**
     * Active ↔ deactivated.
     *
     * @throws ValidationException when the actor tries to deactivate their own account
     */
    public function toggleStatus(User $actor, int $id): User
    {
        $user = $this->users->findOrFail($id);
        $this->assertTargetManageable($actor, $user);
        $newStatus = $user->account_status === 'active' ? 'deactivated' : 'active';

        if ($newStatus === 'deactivated') {
            $this->assertNotSelf($actor, $user);
        }

        $this->users->update($user, ['account_status' => $newStatus]);
        if ($newStatus === 'deactivated') {
            $this->users->revokeTokens($user);
        }

        return $user;
    }

    /** 12 random letters and digits (no symbols, so it can be read out or typed easily). */
    public static function temporaryPassword(): string
    {
        return Str::password(12, letters: true, numbers: true, symbols: false);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{full_name: string, username: string, email: string, role: string, location_id: ?int}
     */
    private function profile(array $data): array
    {
        return [
            'full_name' => trim((string) $data['full_name']),
            'username' => trim((string) $data['username']),
            'email' => trim((string) $data['email']),
            'role' => (string) $data['role'],
            'location_id' => ! empty($data['location_id']) ? (int) $data['location_id'] : null,
        ];
    }

    private function assertRoleAssignmentAllowed(User $actor, string $role): void
    {
        if ($role === 'Developer' && ! $actor->isDeveloper()) {
            throw new AccessDeniedHttpException('Only a Developer may assign the Developer role.');
        }
    }

    private function assertTargetManageable(User $actor, User $target): void
    {
        if ($target->isDeveloper() && ! $actor->isDeveloper()) {
            throw new AccessDeniedHttpException('The Developer account may only be managed by a Developer.');
        }
    }

    private function assertNotSelf(User $actor, User $target): void
    {
        if ($actor->is($target)) {
            throw ValidationException::withMessages(['account_status' => 'You cannot deactivate your own account.']);
        }
    }
}
