<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Support\Facades\Log;

class LogLockout
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
        Log::warning('⛔ User Locked Out (Too many attempts)', [
            'ip' => request()->ip(),
            'username_attempted' => $event->request->input('username'),
            'time' => now()->toDateTimeString(),
        ]);
    }
}
