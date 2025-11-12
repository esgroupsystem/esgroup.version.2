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
        return [
            'title' => 'New Job Order Created',
            'message' => "Job Order #{$this->jobOrder->id} ({$this->jobOrder->job_type}) has been created for bus {$this->jobOrder->bus_detail->body_number}.",
        ];
    }
}
