<?php

namespace App\Services;

use App\Mail\LeaveNoticeMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class LeaveNotificationService
{
    public function sendToHrOfficers($leave, string $noticeType, string $category): void
    {
        $hrOfficers = User::whereIn('role', 'HR Officer')
            ->whereNotNull('email')
            ->get();

        if ($hrOfficers->isEmpty()) {
            return;
        }

        foreach ($hrOfficers as $hr) {
            Mail::to($hr->email)->send(new LeaveNoticeMail($leave, $noticeType, $category));
        }
    }
}
