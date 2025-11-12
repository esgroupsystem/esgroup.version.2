<?php

namespace App\Events;

use App\Models\JobOrder;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class JobOrderCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $job;

    public function __construct(JobOrder $job)
    {
        $this->job = $job;
    }
}

