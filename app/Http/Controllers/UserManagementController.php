<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UserManagement\StoreUserRequest;
use App\Http\Requests\UserManagement\UpdateUserRequest;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class UserManagementController extends Controller
{
    public function index(Request $request): Response
    {
        $q = trim((string) $request->input('q', ''));
        $actor = $request->user();

        $users = User::query()
            ->with('roles')
            ->when(! $actor->isDeveloper(), function ($query): void {
                $query->whereDoesntHave('roles', function ($roleQuery): void {
                    $roleQuery->where('name', 'Developer');
                });
            })
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($sub) use ($q): void {
                    $sub->where('full_name', 'like', "%{$q}%")
                        ->orWhere('username', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhereHas('roles', function ($role) use ($q): void {
                            $role->where('name', 'like', "%{$q}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $roles = collect(User::availableAssignableRoles($actor))
            ->map(fn (string $name) => \Spatie\Permission\Models\Role::findByName($name, 'web'))
            ->filter()
            ->sortBy('name')
            ->values();

        $locations = Location::where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('authentication/users/index', [
            'users' => $users->through(fn (User $user): array => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role ?? 'N/A',
                'role_name' => $user->roles->pluck('name')->first() ?? (string) ($user->role ?? ''),
                'location_id' => $user->location_id ? (string) $user->location_id : '',
                'account_status' => $user->account_status,
                'last_online' => $user->last_online?->format('M d, Y h:i A'),
                'updated_at' => $user->updated_at?->format('M d, Y h:i A'),
                'is_self' => $user->is($actor),
                'update_url' => route('authentication.users.update', $user->id),
                'reset_url' => route('authentication.users.reset.password', $user->id),
                'status_url' => route('authentication.users.status', $user->id),
            ]),
            'roles' => $roles->pluck('name')->values(),
            'locations' => $locations->map(fn (Location $location): array => ['value' => (string) $location->id, 'label' => (string) $location->name])->values(),
            'filters' => ['q' => $q],
            // Flashed by store/resetPassword; shown once and never persisted.
            'temporaryPassword' => session('temporary_password') ? [
                'password' => (string) session('temporary_password'),
                'username' => (string) session('temporary_password_user'),
            ] : null,
            'can' => [
                'create' => (bool) $actor?->can('users.create'),
                'update' => (bool) $actor?->can('users.update'),
            ],
            'urls' => [
                'index' => route('authentication.users.index'),
                'store' => route('authentication.users.store'),
            ],
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->assertRoleAssignmentAllowed($request->user(), (string) $request->string('role'));

        $temporaryPassword = $this->generateTemporaryPassword(
            $request->string('full_name')->toString()
        );

        $user = DB::transaction(function () use ($request, $temporaryPassword): User {
            $user = User::create([
                'full_name' => trim($request->string('full_name')->toString()),
                'username' => trim($request->string('username')->toString()),
                'email' => trim($request->string('email')->toString()),
                'role' => $request->string('role')->toString(),
                'location_id' => $request->integer('location_id') ?: null,
                'password' => Hash::make($temporaryPassword),
                'account_status' => 'active',
                'status' => 'offline',
                'must_change_password' => true,
            ]);

            $user->assignRole($request->string('role')->toString());

            return $user;
        });

        return redirect()
            ->route('authentication.users.index')
            ->with('temporary_password', $temporaryPassword)
            ->with('temporary_password_user', $user->username)
            ->with('success', 'User created. The temporary password is shown once below.');
    }

    public function update(UpdateUserRequest $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $actor = $request->user();

        $this->assertTargetManageable($actor, $user);
        $role = $request->string('role')->toString();
        $this->assertRoleAssignmentAllowed($actor, $role);

        DB::transaction(function () use ($request, $user, $role): void {
            $user->update([
                'full_name' => trim($request->string('full_name')->toString()),
                'username' => trim($request->string('username')->toString()),
                'email' => trim($request->string('email')->toString()),
                'role' => $role,
                'location_id' => $request->integer('location_id') ?: null,
                'account_status' => $request->string('account_status')->toString(),
            ]);

            $user->syncRoles([$role]);

            if ($user->account_status !== 'active') {
                $user->tokens()->delete();
            }
        });

        return redirect()
            ->route('authentication.users.index')
            ->with('success', 'User updated successfully!');
    }

    public function resetPassword(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $this->assertTargetManageable($request->user(), $user);

        $temporaryPassword = $this->generateTemporaryPassword(
            $user->full_name
        );

        $user->update([
            'password' => Hash::make($temporaryPassword),
            'must_change_password' => true,
        ]);

        // Invalidate all existing mobile bearer tokens after an administrator reset.
        $user->tokens()->delete();

        return redirect()
            ->route('authentication.users.index')
            ->with('temporary_password', $temporaryPassword)
            ->with('temporary_password_user', $user->username)
            ->with('success', 'Password reset. The temporary password is shown once below.');
    }

    public function status(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $this->assertTargetManageable($request->user(), $user);

        $newStatus = $user->account_status === 'active' ? 'deactivated' : 'active';

        $user->update(['account_status' => $newStatus]);

        if ($newStatus === 'deactivated') {
            $user->tokens()->delete();
        }

        return redirect()
            ->route('authentication.users.index')
            ->with('success', 'Account status updated!');
    }

    private function generateTemporaryPassword(string $fullName): string
    {
        $initials = collect(explode(' ', strtolower(trim($fullName))))
            ->filter()
            ->map(fn (string $name): string => $name[0])
            ->implode('');

        return $initials.'123456';
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
}
