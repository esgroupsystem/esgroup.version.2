<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\JobOrder;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobOrderCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $job;

    public function __construct(JobOrder $job)
    {
        $this->job = $job;
    }
}
