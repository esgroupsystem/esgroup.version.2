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
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->input('q', ''));
        $actor = $request->user();

        $users = User::query()
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

        return view('users.index', compact('users', 'roles', 'locations', 'q'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->assertRoleAssignmentAllowed($request->user(), (string) $request->string('role'));

        $temporaryPassword = $this->generateTemporaryPassword();

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

        $temporaryPassword = $this->generateTemporaryPassword();

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

    private function generateTemporaryPassword(): string
    {
        return Str::random(24);
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
