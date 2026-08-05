<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Notifier
{
    public static function notifyRoles(
        array $roles,
        Mailable $mailable
    ): int {
        $emails = User::query()
            ->whereIn('role', $roles)
            ->whereNotNull('email')
            ->where('email', '<>', '')
            ->pluck('email')
            ->filter(
                fn (string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false
            )
            ->unique()
            ->values()
            ->all();

        if ($emails === []) {
            Log::warning('Role notification skipped because no valid recipients were found.', [
                'roles' => $roles,
                'mailable' => $mailable::class,
            ]);

            return 0;
        }

        Mail::to($emails)->queue(
            $mailable->onQueue('emails')
        );

        Log::info('Role email notification queued.', [
            'roles' => $roles,
            'recipients_count' => count($emails),
            'mailable' => $mailable::class,
        ]);

        return count($emails);
    }
}
