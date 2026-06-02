<?php

namespace App\Events;

use App\Models\MirasolBiometricsLog;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class CrossChexLogCreated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $log;

    public function __construct(MirasolBiometricsLog $log)
    {
        $this->log = $log;
    }

    public function broadcastOn()
    {
        return new Channel('crosschex-logs');
    }

    public function broadcastAs()
    {
        return 'log.created';
    }
}
