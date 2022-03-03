<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Services\GameService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GameExpirationCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:expiry';

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
     * @param GameService $gameService
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
       // $count = Game::count();
       // $skip = 5;
        //$other = $count - $skip;
        $games = Game::where('status', '=', 'processing')->get();

        foreach ($games as $game) {
            if ($game->updated_at <= Carbon::now()->subHours(48)) {
                $this->gameService->finishGameExpired($game);
            }
        }
    }
}
