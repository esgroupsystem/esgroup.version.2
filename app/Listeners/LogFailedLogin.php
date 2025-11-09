<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogFailedLogin
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
        Log::warning('❌ Failed Login Attempt', [
            'username_attempted' => $event->credentials['username'] ?? null,
            'ip'       => request()->ip(),
            'agent'    => request()->userAgent(),
            'time'     => now()->toDateTimeString(),
        ]);
    }
}
