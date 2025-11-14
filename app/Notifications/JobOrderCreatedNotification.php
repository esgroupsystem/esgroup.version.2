<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JobOrderCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(public $jobOrder) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $bus = $this->jobOrder->bus ?? $this->jobOrder->bus_detail ?? null;

        return [
            'title' => 'New Job Order Created',
            'message' => "Job Order #{$this->jobOrder->id} ({$this->jobOrder->job_type}) has been created for bus " .
                ($bus?->body_number ?? 'Unknown Bus') . ".",
        ];
    }
}
