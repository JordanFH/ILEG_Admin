<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CategoriaEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $categoria;

    public function __construct($categoria)
    {
        $this->categoria = $categoria;
    }

    public function broadcastOn()
    {
        return new Channel('channel-name');
    }
}
