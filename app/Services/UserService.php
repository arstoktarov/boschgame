<?php


namespace App\Services;


use App\Models\Answer;
use App\Models\Category;
use App\Models\Game;
use App\Models\Round;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserService
{

    public function createGame(User $inventor) {
        $game = new Game();
        $game->save();
        $game->users()->syncWithoutDetaching($inventor);
        return $game;
    }

    public function createRound(User $inventor, Game $game, $category_id) {
        $gameRound = new Round();
        $gameRound->game_id = $game->id;
        $gameRound->creator_id = $inventor->id;
        $gameRound->category_id = $category_id;
        $gameRound->save();
        return $gameRound;
    }

    public function getCategoryStatistics($user) {

        $category_user_games = Game::query()
            ->select(DB::raw('COUNT(*)'))
            ->join('rounds', 'rounds.game_id', 'games.id')
            ->join('game_users', 'game_users.game_id', 'games.id')
            ->join('users', 'game_users.user_id', 'users.id')
            ->where('users.id', $user->id)
            ->whereColumn('rounds.category_id', 'categories.id');

        $category_user_answers = Answer::query()
            ->select(DB::raw('COUNT(*)'))
            ->join('round_user_answers', 'round_user_answers.answer_id', 'answers.id')
            ->join('users', 'round_user_answers.user_id', 'users.id')
            ->join('questions', 'round_user_answers.question_id', 'questions.id')
            ->where('users.id', $user->id)
            ->whereColumn('questions.category_id', 'categories.id');

        $categories = Category::query()
            ->select([
                'categories.*',
                'games_count' => $category_user_games,
                'answers' => $category_user_answers,
                'correct_answers' => (clone $category_user_answers)->where('is_correct', 1)
            ])->get()->filter(function($category) {
                return $category->answers > 0;
            })->values();

        return $categories;

    }

}