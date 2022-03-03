<?php

namespace App\Console\Commands;

use Faker\Provider\File;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Game;
use App\Models\GameUser;
use App\Models\Round;
use App\Services\GameService;
use App\Push\FirebasePush;
class PlayerNotice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'player:notice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $gameService;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $games = Game::where('status', Game::STATUS_STARTED)->whereNotNull('deadline')->get();

        foreach ($games as $game) {
            $deadline = new Carbon($game['deadline']);
            $diff = $deadline->diffInHours(Carbon::now());
            $firstPlayer = GameUser::where('game_id', $game['id'])->where('player_number', 1)->pluck('user_id')->first();
            $secondPlayer = GameUser::where('game_id', $game['id'])->where('player_number', 2)->pluck('user_id')->first();
            $first = User::find($firstPlayer);
            $second = User::find($secondPlayer);
            $r = [];
            $rounds = Round::where('game_id', $game['id'])->get();

            foreach ($rounds as $round) {
                array_push($r, $round['creator_id']);
            }
            switch ($diff) {
                case 1:
                    if (count($r) == 0) {
                        FirebasePush::sendMessage('До окончания игры с ' . $first['login'] . ' осталось ' . $diff . ' часов', 'Примите вызов', $second);
                    }
                    if (count($r) == 1) {
                        FirebasePush::sendMessage('До окончания игры с ' . $second['login'] . ' осталось ' . $diff . ' часов', 'Примите вызов', $first);
                    }
                    if (count($r) > 1) {
                        if ($r[count($r) - 2] == $firstPlayer) {
                            FirebasePush::sendMessage('До окончания игры с ' . $second['login'] . ' осталось ' . $diff . ' часов', 'Примите вызов', User::find($r[count($r) - 2]));
                        }
                        if ($r[count($r) - 2] == $secondPlayer) {
                            FirebasePush::sendMessage('До окончания игры с ' . $first['login'] . ' осталось ' . $diff . ' часов', 'Примите вызов', User::find($r[count($r) - 2]));
                        }
                    }
                    break;


                case 6:
                    if (count($r) == 0) {
                        FirebasePush::sendMessage('До окончания игры с ' . $first['login'] . ' осталось ' . $diff . ' часов', 'Примите вызов', $second);
                    }
                    if (count($r) == 1) {
                        FirebasePush::sendMessage('До окончания игры с ' . $second['login'] . ' осталось ' . $diff . ' часов', 'Примите вызов', $first);
                    }
                    if (count($r) > 1) {
                        if ($r[count($r) - 2] == $firstPlayer) {
                            FirebasePush::sendMessage('До окончания игры с ' . $second['login'] . ' осталось ' . $diff . ' часов', 'Примите вызов', User::find($r[count($r) - 2]));
                        }
                        if ($r[count($r) - 2] == $secondPlayer) {
                            FirebasePush::sendMessage('До окончания игры с ' . $first['login'] . ' осталось ' . $diff . ' часов', 'Примите вызов', User::find($r[count($r) - 2]));
                        }
                    }
                    break;


                case 24:
                    if (count($r) == 0) {
                        FirebasePush::sendMessage('До окончания игры с ' . $first['login'] . ' осталось ' . $diff . ' часов', 'Примите вызов', $second);
                    }
                    if (count($r) == 1) {
                        FirebasePush::sendMessage('До окончания игры с ' . $second['login'] . ' осталось ' . $diff . ' часов', 'Примите вызов', $first);
                    }
                    if (count($r) > 1) {
                        if ($r[count($r) - 2] == $firstPlayer) {
                            FirebasePush::sendMessage('До окончания игры с ' . $second['login'] . ' осталось ' . $diff . ' часов', 'Примите вызов', User::find($r[count($r) - 2]));
                        }
                        if ($r[count($r) - 2] == $secondPlayer) {
                            FirebasePush::sendMessage('До окончания игры с ' . $first['login'] . ' осталось ' . $diff . ' часов', 'Примите вызов', User::find($r[count($r) - 2]));
                        }
                    }
                    break;
            }
        }

    }
}
