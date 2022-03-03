<?php

namespace App\Events;

use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameUpdatedEvent extends WebsocketEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $game;
    public $user_id;

    /**
     * Create a new event instance.
     *
     * @param int $user_id
     * @param Game $game
     */
    public function __construct(int $user_id, Game $game)
    {
        $this->user_id = $user_id;
        $this->game = $game;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        //return new PrivateChannel('channel-name');
        return ['gameUpdated'];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return $this->socketMessage($this->user_id, GameResource::make($this->game->reloadAllRelations()));
    }

    public function broadcastAs() {
        return 'game.updated';
    }
}
