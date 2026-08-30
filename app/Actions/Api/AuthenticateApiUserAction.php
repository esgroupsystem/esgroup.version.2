<?php

declare(strict_types=1);

namespace App\Actions\Api;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

final class AuthenticateApiUserAction
{
    /** @param array{email: string, password: string} $credentials */
    public function execute(array $credentials, string $ip): ?array
    {
        $email = strtolower(trim($credentials['email']));
        $ipKey = 'api-login:ip:'.$ip;
        $emailKey = 'api-login:email:'.sha1($email);

        if (RateLimiter::tooManyAttempts($ipKey, 20) || RateLimiter::tooManyAttempts($emailKey, 5)) {
            return null;
        }

        if (! Auth::attempt([
            'email' => $email,
            'password' => $credentials['password'],
            'account_status' => 'active',
        ])) {
            RateLimiter::hit($ipKey, 60);
            RateLimiter::hit($emailKey, 60);

            return [];
        }

        RateLimiter::clear($ipKey);
        RateLimiter::clear($emailKey);

        /** @var User $user */
        $user = Auth::user();
        $token = $user->createToken('mobile-token', ['api:access'])->plainTextToken;

        Auth::logout();

        return [
            'token' => $token,
            'expires_in_minutes' => (int) config('sanctum.expiration'),
            'user' => $user,
        ];
    }
}
