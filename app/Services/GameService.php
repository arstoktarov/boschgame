<?php


namespace App\Services;


use App\Events\GameUpdatedEvent;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Models\GameUser;
use App\Models\Round;
use App\Models\Question;
use App\Models\RoundQuestion;
use App\Models\RoundUserAnswer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GameService
{
    public function createGame($creator_id, $status = null) {
        $game = new Game();
        $game->status = $status ?? Game::STATUS_WAITING_FOR_ENEMY;
        $game->creator_id = $creator_id;
        $game->deadline = Carbon::now()->addHours(48);
        $game->save();

        return $game;
    }

    public function createRound(Game $game, int $category_id, $status) {
        $round = new Round();
        $round->game_id = $game->id;
        $round->creator_id = Auth::id();
        $round->category_id = $category_id;
        $round->round_number = $game->rounds()->count();
        $round->status = Round::ROUND_FIRST_TURN;
        $round->save();

        return $round;
    }

    public function createQuestionsForRound(Round $round) {
        $questions = Question::where('category_id', $round->category_id)->inRandomOrder()->limit(Round::QUESTIONS_COUNT)->get();

        foreach ($questions as $question) {
            $roundQuestion = new RoundQuestion([
                'round_id' => $round->id,
                'question_id' => $question->id
            ]);
            $roundQuestion->save();
        }
    }

    public function createRoundAnswers(int $round_id, int $user_id, $answers) {
        $insertValues = [];
        foreach ($answers as $answer) {
            $userAnswer = new RoundUserAnswer([
                'user_id' => $user_id,
                'round_id' => $round_id,
                'question_id' => intval($answer['question_id']),
                'answer_id' => intval($answer['answer_id']),
            ]);
            $userAnswer->save();
        }

        DB::table('round_user_answers')->insert($insertValues);
    }



    public function attachUserToGame($game_id, $user_id, $player_number) {
        $player = GameUser::firstOrCreate([
            'user_id' => $user_id,
            'game_id' => $game_id,
            'player_number' => $player_number,
        ]);

        return $player;
    }

    public function removeUserFromGame($game_id, $user_id) {
        $gameUser = GameUser::where('game_id', $game_id)->where('user_id', $user_id)->first();
        if ($gameUser)
            $gameUser->delete();
    }



    public function getGameWithRelations($game_id) {
        return Game::query()
            ->with([
                'players',
                'rounds',
                'rounds.questions',
                'rounds.questions.answers',
                'rounds.userAnswers',
            ])
            ->findOrFail($game_id);
    }

    public function getProcessingGame() {
        return Game::where('status', Game::STATUS_WAITING_FOR_ENEMY)->whereDoesntHave('players', function (Builder $query) {
            $query->where('user_id', '=', auth()->id());
        })->first();
    }

    public function getRoundWithRelations($game, $round_id) {
        return $game->rounds()
            ->with([
                'questions',
                'questions.answers',
                'userAnswers'
            ])
            ->find($round_id);
    }

    public function getRoundQuestions($game, $roundId) {
        if (!$game->relationLoaded('rounds')) $this->game->load('rounds');

        $round = $game->rounds->where('id', $roundId)->first();
        return $round->questions;
    }

    public function isGameFinished(Game $game) {
        return is_null($game->player_turn);
    }



    public function finishGameOrdinaryWay(Game $game) {

        $firstPlayer = $game->players()->where('player_number', GameUser::FIRST_PLAYER)->first();
        $secondPlayer = $game->players()->where('player_number', GameUser::SECOND_PLAYER)->first();

        $firstPlayerPoints = intval($game->points($firstPlayer->id));
        $secondPlayerPoints = intval($game->points($secondPlayer->id));

        if ($firstPlayerPoints > $secondPlayerPoints)
        {
            $game->winner_id = $firstPlayer->id;
            $game->finish_status = Game::FINISH_STATUS_ORDINARY;

            $firstPlayer->setNewScores($secondPlayer->scores, Game::GAME_WIN_COEFFICIENT);
            $secondPlayer->setNewScores($firstPlayer->scores, Game::GAME_LOSE_COEFFICIENT);
        }
        else if ($firstPlayerPoints < $secondPlayerPoints)
        {
            $game->winner_id = $secondPlayer->id;
            $game->finish_status = Game::FINISH_STATUS_ORDINARY;

            $secondPlayer->setNewScores($secondPlayer->scores, Game::GAME_WIN_COEFFICIENT);
            $firstPlayer->setNewScores($secondPlayer->scores, Game::GAME_LOSE_COEFFICIENT);
        }
        else
        {
            $game->winner_id = null;
            $game->finish_status = Game::FINISH_STATUS_DRAW;

            $firstPlayer->setNewScores($secondPlayer->scores, Game::GAME_DRAW_COEFFICIENT);
            $secondPlayer->setNewScores($secondPlayer->scores, Game::GAME_DRAW_COEFFICIENT);
        }

        $game->status = Game::STATUS_FINISHED;
        $game->save();
        return $game;
    }

    public function finishGameExpired(Game $game) {
        try {
            $winner = $game->players()->where('users.id', '<>', $game->player_turn_id)->first();
            $loser = $game->players()->where('users.id', '=', $game->player_turn_id)->first();

            if (!$winner) {
                Log::info('Expired Game Error: Couldn\'t find winner of the game');
                return;
            }

            $game->winner_id = $winner->id;
            $game->status = Game::STATUS_FINISHED;
            $game->finish_status = Game::FINISH_STATUS_EXPIRED;
            $game->save();
            $game->delete();

//            $winner->setNewScores($loser->scores, Game::GAME_WIN_COEFFICIENT);
//            $loser->setNewScores($winner->scores, Game::GAME_LOSE_COEFFICIENT);
        } catch (\Exception $exception) {
            Log::error('Не могу удалить игру: '.$exception->getMessage());
        }
    }
    /**
     * @param Game $game
     * @param $initiator_id
     */
    public function finishGameConcede(Game $game, $initiator_id) {
        $winner = $game->players()->where('users.id', '<>', $initiator_id)->first();
        $loser = $game->players()->where('users.id', '=', $initiator_id)->first();

        if (!$winner) {
            Log::info('Conceded Game Error: Couldn\'t find winner of the game');
            return;
        }

        if ($game->finishedRounds()->count() >= Game::VALUABLE_GAME_ROUNDS_COUNT) {
            $winner->setNewScores($loser->scores, Game::GAME_WIN_COEFFICIENT);
        }

        $loser->setNewScores($winner->scores, Game::GAME_LOSE_COEFFICIENT);

        $game->winner_id = $winner->id;
        $game->status = Game::STATUS_FINISHED;
        $game->finish_status = Game::FINISH_STATUS_CONCEDE;
        $game->save();
    }



    public function touchGame(Game $game) {
        $game->updated_at = Carbon::now();
        $game->save();
    }

    public function eventGameUpdated($user_id, $game) {
        $game = $game->reloadAllRelations();
        event(new GameUpdatedEvent($user_id, $game));
    }

    public function gameWinner($user_id,Game $game)
    {
        $game->winner_id = $user_id;
        $game->save();
    }

    public function gameFinish(Game $game)
    {
        $game->status = Game::STATUS_FINISHED;
        $game->finish_status = 'ordinary';
        $game->save();
    }

    public function gameFinishExpired(Game $game, $winner, $loser)
    {
        $win = User::where('id', $winner)->first();
        $lose = User::where('id', $loser)->first();
        $game->status = Game::STATUS_FINISHED;
        $game->finish_status = Game::FINISH_STATUS_EXPIRED;
        $game->save();
        $win->setNewScores($lose->scores, Game::GAME_WIN_COEFFICIENT);
        $lose->setNewScores($win->scores, Game::GAME_LOSE_COEFFICIENT);
    }
}
