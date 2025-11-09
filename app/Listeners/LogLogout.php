<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
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
            'user_id'  => $event->user->id,
            'username' => $event->user->username,
            'ip'       => request()->ip(),
            'time'     => now()->toDateTimeString(),
        ]);
    }
}
