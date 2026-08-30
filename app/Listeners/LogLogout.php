<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Support\Facades\Log;

class LogLogout
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        Log::info('✅ User Logged Out', [
            'user_id' => $event->user->id,
            'username' => $event->user->username,
            'ip' => request()->ip(),
            'time' => now()->toDateTimeString(),
        ]);
    }
}
