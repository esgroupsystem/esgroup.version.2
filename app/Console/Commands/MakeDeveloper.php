<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Security\UserManagementService;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

/**
 * The only way to give an account the Developer role (it is hidden from Roles and Users).
 * Needs server access, so it cannot be done from the browser.
 */
final class MakeDeveloper extends Command
{
    protected $signature = 'security:make-developer {username : Username of an existing account}';

    protected $description = 'Give an existing user the Developer role (every permission). Command line only.';

    public function handle(UserManagementService $users): int
    {
        $username = (string) $this->argument('username');

        if (! $this->confirm("Give \"{$username}\" the Developer role? Developers have every permission.", false)) {
            $this->info('Cancelled.');

            return self::SUCCESS;
        }

        try {
            $user = $users->makeDeveloper($username);
        } catch (ValidationException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("{$user->username} is now a Developer.");

        return self::SUCCESS;
    }
}
