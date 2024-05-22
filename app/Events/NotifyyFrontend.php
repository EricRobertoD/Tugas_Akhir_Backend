<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyyFrontend implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $channel;

    public function __construct($message, $channel)
    {
        $this->message = $message;
        $this->channel = $channel;
        Log::info('NotifyyFrontend event constructed with message: ' . $message);
    }

    public function broadcastOn(): array
    {
        Log::info('NotifyyFrontend event broadcasting on channel: channel1');
        return [new Channel($this->channel)];
    }
}
