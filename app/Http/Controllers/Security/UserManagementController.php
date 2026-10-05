<?php

declare(strict_types=1);

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Http\Requests\Security\StoreUserRequest;
use App\Http\Requests\Security\UpdateUserRequest;
use App\Http\Resources\Security\UserRowResource;
use App\Models\User;
use App\Services\Security\UserManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** Security → Users (authentication.users.*). */
final class UserManagementController extends Controller
{
    public function __construct(private readonly UserManagementService $userService) {}

    public function index(Request $request): Response
    {
        $q = trim((string) $request->input('q', ''));
        $actor = $request->user();

        return Inertia::render('authentication/users/index', [
            'users' => $this->userService->paginate($actor, $q)
                ->through(fn (User $user): array => UserRowResource::make($user)->resolve($request)),
            'roles' => $this->userService->assignableRoles($actor),
            // A user tied to a stockroom only sees and uses that stockroom in Inventory.
            'locations' => $this->userService->locationOptions(),
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
        ['user' => $user, 'password' => $password] = $this->userService->create($request->user(), $request->validated());

        return redirect()
            ->route('authentication.users.index')
            ->with('temporary_password', $password)
            ->with('temporary_password_user', $user->username)
            ->with('success', 'User created. The temporary password is shown once below.');
    }

    public function update(UpdateUserRequest $request, int $id): RedirectResponse
    {
        $this->userService->update($request->user(), $id, $request->validated());

        return redirect()->route('authentication.users.index')->with('success', 'User updated successfully!');
    }

    public function resetPassword(Request $request, int $id): RedirectResponse
    {
        ['user' => $user, 'password' => $password] = $this->userService->resetPassword($request->user(), $id);

        return redirect()
            ->route('authentication.users.index')
            ->with('temporary_password', $password)
            ->with('temporary_password_user', $user->username)
            ->with('success', 'Password reset. The temporary password is shown once below.');
    }

    public function status(Request $request, int $id): RedirectResponse
    {
        $this->userService->toggleStatus($request->user(), $id);

        return redirect()->route('authentication.users.index')->with('success', 'Account status updated!');
    }
}
