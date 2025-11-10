<?php

namespace App\Helpers;

use App\Models\User;

use Illuminate\Support\Facades\Mail;

class Notifier
{
    public static function notifyRoles(array $roles, $mailable)
    {
        $emails = User::whereIn('role', $roles)
            ->pluck('email')
            ->toArray();

        if (!empty($emails)) {
            Mail::to($emails)->send($mailable);
        }
    }
}
