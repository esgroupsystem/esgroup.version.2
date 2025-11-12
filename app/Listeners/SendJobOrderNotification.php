<?php

namespace App\Listeners;

use App\Events\JobOrderCreated;
use App\Models\User;
use App\Notifications\JobOrderCreatedNotification;

class SendJobOrderNotification
{
    /**
     * Handle the event.
     */
    public function handle(JobOrderCreated $event): void
    {
        // Targeted roles
        $rolesToNotify = ['IT Officer', 'Safety Officer', 'IT Head', 'Head Inspector'];

        $users = User::whereIn('role', $rolesToNotify)->get();

        foreach ($users as $user) {
            $user->notify(new JobOrderCreatedNotification($event->job));
        }
    }
}
