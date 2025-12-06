<?php

namespace App\Listeners;

use App\Events\POCreated;
use App\Models\User;
use App\Notifications\POCreatedNotification;

class SendPOCreatedNotification
{
    public function handle(POCreated $event): void
    {
        // Maintenance-related users
        $rolesToNotify = ['Maintenance Head', 'Maintenance Officer', 'Maintenance Staff', 'Maintenance Engineer'];

        // Match your JobOrder relationship (users.role column)
        $users = User::whereIn('role', $rolesToNotify)->get();

        foreach ($users as $user) {
            $user->notify(new POCreatedNotification($event->po));
        }
    }
}
