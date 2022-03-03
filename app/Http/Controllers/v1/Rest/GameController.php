<?php

namespace App\Http\Controllers\v1\Rest;

use App\Events\GameUpdatedEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\GameResource;
use App\Http\Resources\RoundWithQuestionsResource;
use App\Models\User;
use App\Models\Game;
use App\Models\Round;
use App\Models\RoundQuestion;
use App\Models\RoundUserAnswer;
use App\Models\GameUser;
use App\Models\Question;
use App\Push\FirebasePush;
use App\Services\GameListService;
use App\Services\GameService;
use App\Services\UserService;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Authenticatable;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Services\PushService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Monolog\Formatter\WildfireFormatter;
use mysql_xdevapi\Table;
use PhpOffice\PhpSpreadsheet\Calculation\Category;
use function Complex\sec;
use function Composer\Autoload\includeFile;
use function GuzzleHttp\Psr7\str;
use function React\Promise\all;

class GameController extends Controller
{
    protected $gameService;

    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
    }

    /**
     * Начинает игру с рандомным противником
     *
     * @param Request $request
     * @return GameResource
     */
    public function randomGame(Request $request)
    {
        $game = $this->gameService->getProcessingGame();
//        $should_create_game = false;
//        if (!$game) {
//            $should_create_game = true;
//        }
//        else {
//            $game_player = $game->players()->where('user_id', '<>', Game::DEFAULT_PLAYER_ID)->first();
//            try {
//                $should_create_game = !auth()->user()->activeGames()->whereHas('players', function ($query) use ($game_player) {
//                    $query->where('users.id', $game_player->id);
//                })->exists();
//            } catch (\Throwable $exception) {
//            }
//        }
        $should_create_game = false;
        if ($game){
            if ($game->created_at >= Carbon::now()->subMinutes(5) && $game->creator_id != \auth()->id()) {
                $should_create_game = false;
            }
            else {
                $should_create_game = true;
            }
        }
        if (!$game) {
            $should_create_game = true;
        }

        $secondPlayer = Game::where('status', 'processing')->where('created_at', '>=', now()->subMinutes(5)->format(
                'Y-m-d H:m:i'
        ))->where('creator_id','!=', auth()->id())->first();
        # First player who created game
        $activeGames = Game::where('status', 'started')->where('creator_id', auth()->id())->pluck('id');
        $ac = json_decode(json_encode($activeGames, true));
        $enemys = [];
        for ($i = 0; $i < count($ac); $i++) {
            $enemy = GameUser::where('game_id', $ac[$i])->where('player_number', 1)->pluck('user_id');
            $e = json_decode(json_encode($enemy, true));
            array_push($enemys, $e[0]);
        }

        #Second player who joined game
        $enemysSecond = [];
        $secondEnemys = [];
        $secondEnemyGames = json_decode(json_encode(GameUser::where('user_id', auth()->id())->pluck('game_id')));
        for ($i = 0; $i < count($secondEnemyGames); $i++) {
            $secondEnemys = GameUser::where('game_id', $secondEnemyGames[$i])->where('player_number', 2)->pluck('user_id')->toArray();
            array_push($enemysSecond, $secondEnemys);
        }
        if ($should_create_game) {
            $game = $this->gameService->createGame(auth()->id(), Game::STATUS_WAITING_FOR_ENEMY);
        }
//        if (empty($secondPlayer) || in_array($secondPlayer->creator_id, $enemys)
//            || in_array($secondPlayer->creator_id,$secondEnemys)) {
        if ($secondPlayer) {
            if (in_array($secondPlayer->creator_id, $enemys) || in_array($secondPlayer->creator_id, $secondEnemys)) {
                $game = $this->gameService->createGame(auth()->id(), Game::STATUS_WAITING_FOR_ENEMY);
            } else {
                $this->gameService->attachUserToGame($game->id, auth()->id(), GameUser::FIRST_PLAYER);
                $this->gameService->attachUserToGame($game->id, $secondPlayer->creator_id, GameUser::SECOND_PLAYER);
                $game->status = Game::STATUS_STARTED;
                $game->save();
            }
        }

        $game = $game->reloadAllRelations();

        return new GameResource($game);
    }

    /**
     * Начинает игру с другом или возвращает активную игру
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function startGameWithFriend(Request $request)
    {
        $rules = [
            'friend_id' => 'required'
        ];
        $this->validate($request, $rules);

        if ($request['friend_id'] == Game::DEFAULT_PLAYER_ID)
            return self::errorResponse('You cannot play with this player', 400);

        $enemy = User::find($request['friend_id']);

        if (!$enemy)
            return self::errorResponse('User not found', 404);

        if ($enemy->blacklistedUsers()->where('user_id', auth()->id())->exists())
            return self::errorResponse('You are in blacklist', 400);

        if ($enemy->id == auth()->id())
            return self::errorResponse('You cannot play with yourself', 400);

        $game = auth()->user()->games()->where('status', Game::STATUS_STARTED)
            ->whereHas('players', function (Builder $query) use ($enemy) {
                $query->where('users.id', $enemy->id);
            })->first();

        $beforeGame = DB::table('users')->where('id', '=', auth()->id())->value('scores');

        if (!$game) {
            $game = $this->gameService->createGame($enemy->id, Game::STATUS_WAITING_FOR_ENEMY);
            $this->gameService->attachUserToGame($game->id, auth()->id(), GameUser::FIRST_PLAYER);
            $this->gameService->attachUserToGame($game->id, $enemy->id, GameUser::SECOND_PLAYER);
            $game->status = Game::STATUS_STARTED;
            $game->save();


            FirebasePush::sendMessage(auth()->user()->first_name . ' бросил вам вызов', 'Примите вызов', $enemy);
        }
        $afterGame = DB::table('users')->where('id', '=', auth()->id())->value('scores');
        $pointZaLastgame = $beforeGame - @$afterGame;

        $game = new GameResource($game->reloadAllRelations());

        return self::Response(200, $game);
    }

    /**
     * Создаёт новый раунд только при необходимости
     * в обратном случае вернёт ошибку
     *
     * @param Game $game
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function startNewRound(Game $game, Request $request)
    {
        $rules = [
            'category_id' => 'required'
        ];
        $this->validate($request, $rules);

        if ($this->gameService->isGameFinished($game)) return self::errorResponse('Game already finished', 400);
        if ($game->player_turn != Auth::id()) return self::errorResponse('This is not your turn', 400);
        if ($game->hasActiveRounds()) return self::errorResponse('Please wait until previous round has ended', 400);

        $round = $this->gameService->createRound($game, $request['category_id'], Round::ROUND_FIRST_TURN);

        $this->gameService->createQuestionsForRound($round);

        $round = $this->gameService->getRoundWithRelations($game, $round->id);

        $this->gameService->touchGame($game);

        return self::Response(200, new RoundWithQuestionsResource($round));
    }

    /**
     * Создает ответы к раунду на основе полученных данных
     *
     * @param Round $round
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function answerRound(Round $round, Request $request)
    {
        $rules = [
            'answers' => [
                'array',
                'size:' . Round::QUESTIONS_COUNT,
                'required'
            ],
            'answers.*.question_id' => 'required',
            'answers.*.answer_id' => 'required'
        ];
        //FirebasePush::sendMessage('hello' ,' asdasdas: ', User::where('id', 99)->first());
        $this->validate($request, $rules);

        $game = $round->game;
        $game->load('rounds');

        if ($game->status == Game::STATUS_FINISHED) return self::errorResponse('Game already finished', 400);
        if ($game->player_turn != Auth::id() || $round->player_turn != Auth::id()) return self::errorResponse('This is not your turn', 400);

        if ($this->gameService->isGameFinished($game)) {
            return self::errorResponse('Game Already finished', 400);
        }

        $this->gameService->createRoundAnswers($round->id, auth()->id(), $request['answers']);

        $game->refresh();
        $game->load('rounds');

        if ($game->player_turn != Auth::id() || $round->player_turn != Auth::id()) {

        }

        if ($this->gameService->isGameFinished($game)) {
            $this->gameService->finishGameOrdinaryWay($game);
        }

        $this->gameService->touchGame($game);

        $game = $game->reloadAllRelations();

        $user = User::where('id', auth()->id())->first();
        $push = (new PushService())->sendNewPlayerTurn($user, $game);

        return self::Response(200, new GameResource($game));
    }

    /**
     * Создаёт ответы автоматически (неправильные)
     *
     * @param Round $round
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function setIncorrectAnswers(Round $round, Request $request)
    {
        $answers = [];
        foreach ($round->questions as $question) {
            $answers[] =
                [
                    'question_id' => $question->id,
                    'answer_id' => $question->answers()->where('is_correct', 0)->first()->id
                ];
        }
        $request['answers'] = $answers;

        return $this->answerRound($round, $request);
    }

    /**
     * Action для того чтобы сдаться в игре
     *
     * @param Game $game
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function surrenderGame(Game $game, Request $request)
    {

        if (!auth()->user()->games()->where('games.id', $game->id)->exists()) return self::errorResponse('You have no permissions', 400);

        if ($game->status != Game::STATUS_STARTED) return self::errorResponse('Game finished or didn\'t started yet', 400);

        $this->gameService->finishGameConcede($game, auth()->id());

        $game->reloadAllRelations();

        $this->gameService->touchGame($game);

        return self::Response(200, new GameResource($game));
    }

    /**
     * Вовращает модельку игры
     *
     * @param Game $game
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGame(Game $game)
    {
        $game->reloadAllRelations();

        return self::Response(200, new GameResource($game));
    }

    /**
     * Возвращает модель раунда
     *
     * @param $roundId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRound($roundId)
    {
        $gameRound = Round::findOrFail($roundId);
        return self::Response(200, new RoundWithQuestionsResource($gameRound->load('userAnswers', 'userAnswers.answer:id,is_correct')));
    }

    /**
     * Возвращает игры в которых настала очередь для ответа у авторизованного пользователя
     *
     * @param GameListService $gameListService
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWaitingGames(GameListService $gameListService)
    {

        $games = $gameListService->getWaitingGames(auth()->user());

        return self::Response(200, GameResource::collection($games));
    }

    /**
     * @param GameListService $gameListService
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyTurnGames(GameListService $gameListService)
    {
        $games = $gameListService->getMyTurnGames(auth()->user());
        return self::Response(200, GameResource::collection($games));
    }

    public function pointZaLastGame($game_id)
    {
        $rules = [
            'game_id' => 'required'
        ];
//        $this->validate($game_id, $rules);
        $user = auth()->user();
        $enemy = DB::table('game_users')->where('game_id', '=', $game_id)->where('user_id','<>' , $user['id'])->value('user_id');
        $afterGame = DB::table('users')->where('id', '=', auth()->id())->value('scores'); //user scores
        $enemyScore = DB::table('users')->where('id', '=', $enemy)->value('scores'); //enemy scores
        $winner_id = DB::table('games')->where('id', '=', $game_id)->value('winner_id'); //winner id

        if(auth()->id() == $winner_id){ //если победил
            $gameCoef = 1;
        } elseif ($winner_id == NULL){ // если ничья
            $gameCoef = 0.5;
        } else {  // проигрыш
            $gameCoef = 0;
        }

        $point = $user->calculateNewScores($enemyScore, $gameCoef);
        $pointzalastgame = $point - $afterGame;
        $game = Game::where('id', $game_id)->first();

        return response()->json([
            'Points' => round($pointzalastgame),
            'game' =>  $game
        ]);
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActiveGames()
    {
        $games = auth()->user()->activeGames;

        return self::Response(200, GameResource::collection($games));
    }

    /**
     * @param GameListService $gameListService
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActiveAllGames(GameListService $gameListService)
    {
        $response = $gameListService->getAll(auth()->user(), 6);

        return self::Response(200, $response);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFinishedGames()
    {
        $games = auth()->user()->games()->where('status', Game::STATUS_FINISHED);

        $games = $this->paginatedToResourceCollection($games->paginate(5), GameResource::class);

        return self::Response(200, $games);
    }
}
