<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Api\AuthenticateApiUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthController extends Controller
{
    public function __construct(
        private readonly AuthenticateApiUserAction $authenticate,
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $email = strtolower(trim((string) $credentials['email']));

        $result = $this->authenticate->execute([
            'email' => $email,
            'password' => (string) $credentials['password'],
        ], (string) $request->ip());

        if ($result === null) {
            return response()->json([
                'message' => 'Too many login attempts. Please try again later.',
            ], 429);
        }

        if ($result === []) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        return response()->json($result);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out',
        ]);
    }
}
