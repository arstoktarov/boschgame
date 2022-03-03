<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\Game;
use App\Models\GameUser;
use App\Models\Round;
use App\Services\GameService;
use Illuminate\Support\Facades\Log;

class CheckAllGames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:check';

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
            if (Carbon::now() > $game['deadline']) {
                $rounds = Round::where('game_id', $game['id'])->get();
                $aar = [];
                foreach ($rounds as $round) {
                    array_push($aar, $round['creator_id']);
                }
                $firstPlayer = GameUser::where('game_id', $game['id'])->where('player_number', 1)->pluck('user_id')->first();
                $secondPlayer = GameUser::where('game_id', $game['id'])->where('player_number', 2)->pluck('user_id')->first();
                if (end($aar) == $firstPlayer) {
                    $this->gameService->gameWinner($firstPlayer, $game);
                    $this->gameService->gameFinishExpired($game, $firstPlayer, $secondPlayer);
                }
                else {
                    $this->gameService->gameWinner($secondPlayer, $game);
                    $this->gameService->gameFinishExpired($game,$secondPlayer, $firstPlayer);
                }
            }
        }
//        Log::info('test');
    }
}
