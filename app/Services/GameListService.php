<?php


namespace App\Services;


use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GameListService
{
    /**
     * @param User $user
     * @return Collection
     */
    public function getActiveGames(User $user) {
        return $user->activeGames()
            ->orderBy('created_at', 'desc')
            ->withPlayers()
            ->withRounds()
            ->get();
    }

    public function getWaitingGames(User $user) {
        return $this->getActiveGames($user)
            ->filter(function($game) use ($user) {
                return $game->player_turn != $user->id;
            })->values();
    }

    public function getMyTurnGames(User $user) {
        return $this->getActiveGames($user)
            ->filter(function($game) use ($user) {
                return $game->player_turn == $user->id;
            })
            ->values();
    }

    public function getLastFinishedGames(User $user, int $count) {
        return $user->games()
            ->withPlayers()
            ->withRounds()
            ->where('status', Game::STATUS_FINISHED)
            ->limit($count)
            ->orderBy('updated_at','desc')
            ->get();
    }

    public function getAll(User $user, int $finishedGamesLimit) {
        return [
            'myTurn' => GameResource::collection($this->getMyTurnGames($user)),
            'waiting' => GameResource::collection($this->getWaitingGames($user)),
            'finished' => GameResource::collection($this->getLastFinishedGames($user, $finishedGamesLimit)),
        ];
    }
}