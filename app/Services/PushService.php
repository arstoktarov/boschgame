<?php


namespace App\Services;
use App\Models\Game;
use App\Models\User;
use App\Push\FirebasePush;

class PushService
{
    public function sendNewPlayerTurn(User $answeredUser, Game $game) {

        $enemy = $game->players->where('user_id', '!=', $answeredUser->id)->first();

        if ($game->player_turn) {
            $title = "$answeredUser->login ответил(-а), теперь ваша очередь!";
            $body = "$answeredUser->login ожидает вашего хода ;)";
        }
        else {
            $title = "$answeredUser->login ответил(-a), игра закончена!";
            $body = "Скорее проверьте результаты игры!";
        }


        if($game->player_turn != auth()->id()) {
            $enemy1 = User::where('id', $game->player_turn)->first();
            //$enemy1 = User::where('id', $enemy->id)->first();
            FirebasePush::sendMessage($title, $body, $enemy1, [
                'game_id' => $game->id,
            ]);
        }
    }
}
