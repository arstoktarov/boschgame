<?php

namespace App\Events;

use App\Models\Game;
use App\Models\User;
use App\Services\GameListService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GamesListUpdatedEvent extends WebsocketEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user_id;

    /**
     * Create a new event instance.
     *
     * @param int $user_id
     */
    public function __construct(int $user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return ['gameListUpdated'];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        $user = User::withoutDefaultPlayer()->findOrFail($this->user_id);
        $gameListService = new GameListService();

        return $this->socketMessage($this->user_id, $gameListService->getAll($user, Game::LAST_FINISHED_COUNT));
    }

    public function broadcastAs() {
        return 'game.list.updated';
    }
}
