<?php

namespace App\Observers;

use App\Events\GamesListUpdatedEvent;
use App\Events\GameUpdatedEvent;
use App\Models\Game;
use Illuminate\Support\Facades\Cache;

class GameObserver
{
    /**
     * Handle the game "created" event.
     *
     * @param  \App\Models\Game  $game
     * @return void
     */
    public function created(Game $game)
    {
        //
    }

    /**
     * Handle the game "updated" event.
     *
     * @param  \App\Models\Game  $game
     * @return void
     */
    public function updated(Game $game)
    {
        $players = $game->players()->withoutDefaultPlayer()->get();
        foreach ($players as $player) {
            event(new GameUpdatedEvent($player->id, $game));
            event(new GamesListUpdatedEvent($player->id));
        }
    }

    /**
     * Handle the game "deleted" event.
     *
     * @param  \App\Models\Game  $game
     * @return void
     */
    public function deleted(Game $game)
    {
        //
    }

    /**
     * Handle the game "restored" event.
     *
     * @param  \App\Models\Game  $game
     * @return void
     */
    public function restored(Game $game)
    {
        //
    }

    /**
     * Handle the game "force deleted" event.
     *
     * @param  \App\Models\Game  $game
     * @return void
     */
    public function forceDeleted(Game $game)
    {
        //
    }
}
