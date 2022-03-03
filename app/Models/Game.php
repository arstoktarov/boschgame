<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Game;
/**
 * @property mixed status
 * @property mixed creator_id
 *
 * @property mixed rounds
 * @property mixed players
 * @property mixed created_at
 * @property mixed updated_at
 */
class Game extends Model
{
    const STATUS_WAITING_FOR_ENEMY = 'processing';
    const STATUS_STARTED = 'started';
    const STATUS_FINISHED = 'finished';

    const FINISH_STATUS_ORDINARY = 'ordinary';
    const FINISH_STATUS_DRAW = 'draw';
    const FINISH_STATUS_EXPIRED = 'expired';
    const FINISH_STATUS_CONCEDE = 'concede';

    const GAME_WIN_COEFFICIENT = 1;
    const GAME_LOSE_COEFFICIENT = 0;
    const GAME_DRAW_COEFFICIENT = 0.5;

    const ROUNDS_COUNT = 6;
    CONST VALUABLE_GAME_ROUNDS_COUNT = 3;
    const LAST_FINISHED_COUNT = 5;

    const DEFAULT_PLAYER_ID = 0;

    protected $usersPoints;

    protected $hidden = ['created_at', 'updated_at'];

    //protected $appends = ['player_turn'];

    public function getActiveGamesCacheTag() {
        return self::ACTIVE_GAMES_CACHE_TAG.$this->id;
    }

    public function rounds() {
        return $this->hasMany(Round::class);
    }

    public function players() {
        return $this->belongsToMany(User::class, 'game_users')
            ->withPivot('points')
            ->select([
                'game_users.game_id',
                'users.*',
                'points' => RoundUserAnswer::query()
                    ->select(DB::raw('COUNT(round_user_answers.id)'))
                    ->join('answers', 'round_user_answers.answer_id', '=','answers.id')
                    ->join('rounds', 'round_user_answers.round_id', '=', 'rounds.id')
                    ->join('games', 'rounds.game_id', '=', 'games.id')
                    ->whereColumn('game_users.game_id', 'games.id')
                    ->whereColumn('users.id', 'round_user_answers.user_id')
                    ->where('is_correct', 1)
            ]);
    }

    public function points($user_id) {
        return RoundUserAnswer::query()
            ->join('answers', 'round_user_answers.answer_id', '=','answers.id')
            ->join('rounds', 'round_user_answers.round_id', '=', 'rounds.id')
            ->join('games', 'rounds.game_id', '=', 'games.id')
            ->where('games.id', $this->id)
            ->where('user_id', $user_id)
            ->where('is_correct', 1)
            ->count();
    }

    public function activeRounds() {
        return $this->rounds->filter(function ($round) {
            return !is_null($round->player_turn);
        });
    }

    public function finishedRounds() {
        return $this->rounds->filter(function ($round) {
            return is_null($round->player_turn);
        });
    }

    public function hasActiveRounds() {
        return $this->activeRounds()->count() > 0;
    }

    /**
     * Возвращает id пользователя очередь которого в игре на момент
     *
     * Если игра закончена или
     * количество раундов равно или превышает максимум обозначенный в констане ROUNDS_COUNT -
     * возвращает NULL
     *
     * @return mixed|null
     */
    public function getPlayerTurnAttribute() {
        if ($this->status == self::STATUS_FINISHED) return null;

        if ($this->rounds->count() == 0) return $this->creator_id;

        $activeRound = $this->activeRounds()->first();

        if (!$activeRound) {
            if ($this->rounds->count() < Game::ROUNDS_COUNT) {
                $lastRound = $this->rounds->sortBy('round_number')->last();
                $nextCreator = $this->players->where('id', '!=', $lastRound->creator_id)->first();
                if (!$nextCreator) return Game::DEFAULT_PLAYER_ID;
                return $nextCreator->id;
            }
            else return null;
        }

        return $activeRound->player_turn;
    }

    public function getShouldCreateRoundAttribute() {
        return !$this->hasActiveRounds();
    }

    public function scopeWithAllRelations($query) {
        return $query->withRounds()
            ->withRoundQuestions()
            ->with([
                'players',
            ]);
    }

    public function scopeWithRounds($query) {
        return $query->with([
            'rounds',
            'rounds.category',
            'rounds.game',
            'rounds.game.players',
            'rounds.userAnswers',
        ]);
    }

    public function scopeWithPlayers($query) {
        return $query->with(['players']);
    }

    public function scopeWithRoundQuestions($query) {
        return $query->with([
            'rounds.questions',
            'rounds.questions.answers',
            'rounds.userAnswers',
            'rounds.userAnswers.answer',
        ]);
    }

    public function reloadAllRelations() {
        return $this->load([
            'players',
            'rounds',
            'rounds.category',
            'rounds.game',
            'rounds.game.players',
            'rounds.questions',
            'rounds.questions.answers',
            'rounds.userAnswers',
            'rounds.userAnswers.answer',
        ]);
    }
}
