<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogSuccessfulLogin
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
        Log::info('✅ Successful Login', [
            'user_id'  => $event->user->id,
            'username' => $event->user->username,
            'ip'       => request()->ip(),
            'agent'    => request()->userAgent(),
            'time'     => now()->toDateTimeString(),
        ]);
    }
}
