<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static findOrFail($roundId)
 */
class Round extends Model
{

    const ROUND_FIRST_TURN = 0;
    const ROUND_SECOND_TURN = 1;
    const ROUND_FINISHED = 2;

    const QUESTIONS_COUNT = 3;

    protected $hidden = ['created_at', 'updated_at', 'category_id', 'game_id'];

    //protected $appends = ['player_turn'];

    //protected $with = ['game'];

    //protected $touches = ['game'];

    public function questions() {
        return $this->belongsToMany(Question::class, 'round_questions');
    }

    public function gameRoundQuestions() {
        return $this->hasMany(RoundQuestion::class);
    }

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function game() {
        return $this->belongsTo(Game::class);
    }

    public function userAnswers() {
        return $this->hasMany(RoundUserAnswer::class);
    }

    public function scopeWithQuestionsAndUserAnswers(Builder $query) {
        return $query->with([
            'questions',
            'questions.answers',
            'userAnswers',
            'userAnswers.answer',
        ]);
    }

    public function getPlayerTurnAttribute() {
        $creator_id = $this->creator_id;

        $userAnswers = $this->userAnswers;

        $creatorAnswersCount = $userAnswers->where('user_id', $creator_id)->count();
        $secondAnswersCount = $userAnswers->where('user_id', '!=', $creator_id)->count();

        $game = $this->game;
        $players = $game->players;

        $second = $players->where('user_id', '!=', $creator_id)->first();
        try {
            if ($creatorAnswersCount < self::QUESTIONS_COUNT) {
                return $players->where('id', $creator_id)->first()->id;
            } else if ($secondAnswersCount < self::QUESTIONS_COUNT) {
                return $players->where('id', '!=', $creator_id)->first()->id ?? Game::DEFAULT_PLAYER_ID;
            } else {
                return null;
            }
        } catch (\Throwable $exception) {
            return null;
        }
    }
}
