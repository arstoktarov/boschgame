<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\UserResource;
use App\Models\Car;
use App\Models\CarTravel;
use App\Models\CarTravelPlace;
use App\Models\Category;
use App\Models\CommentDislike;
use App\Models\CommentLike;
use App\Models\Favorite;
use App\Models\Post;
use App\Models\Round;
use App\Models\Game;
use App\Models\GameUser;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CategoryController extends Controller

{

    public function index(Request $request)
    {
        $data['categories'] = Category::all();
        return view('admin.category.index', $data);
    }

    public function show($id)
    {
        $data['cat'] = Category::findOrFail($id);

        return response()->view('admin.category.show',$data);
    }

    public function edit($id, Request $request)
    {
        $data['cat'] = Category::findOrFail($id);

        return view('admin.category.edit',$data);

    }
    public function add()
    {
        return view('admin.category.add');

    }
    public function create(Request $request)
    {
        $cat = new Category();
        $cat->title = $request['title'];
        $cat->image = $request['image'];
        $cat->save();

        return redirect()->route('admin.category.index');

    }

    public function update($id, Request $request)
    {

        $cat = Category::findOrFail($id);
        if ($request['title']){
            $cat->title = $request['title'];
            $cat->save();
        }

        if ($request['image']){
            $cat->image = $request['image'];
            $cat->save();
        }
        $cat->save();
        return redirect()->route('admin.category.index');
    }

    public function destroy($id)
    {
        $l = Category::findOrFail($id);
        $l->delete();
        return redirect()->back();
    }


    public function games($id)
    {
        $rounds = Round::where('category_id', $id)->where("round_number", 0)->get();
        $games = [];
        foreach ($rounds as $round) {
            $games[] = Game::where('id', $round->game_id)->first();
        }

        return view('admin.category.games', ['games' => $games]);
    }

    public function getGame($id)
    {
        $game = Game::where('id', $id)->first();
        $first = GameUser::where('game_id', $game->id)->where('player_number', 1)->first();
        $second = GameUser::where('game_id', $game->id)->where('player_number', 2)->first();
        $firstPlayer = User::where('id', $first->user_id)->first();
        $secondPlayer = User::where('id', $second->user_id)->first();
        $winner = User::where('id', $game->winner_id)->first();
        return view('admin.category.game', ['game' => $game, 'first_player' => $firstPlayer, 'second_player' => $secondPlayer, 'winner' => $winner]);
    }

    public function statisticsIndex(Request $request)
    {
        $games = Game::where('status', Game::STATUS_FINISHED)->get();

        return view('admin.statistics.index', ['games' => $games]);
    }

    public function getByMonth(Request $request)
    {
        $games = Game::where('status', Game::STATUS_FINISHED)->get();

        if ($request['month']) {
            $month = Carbon::create($request['month'])->month;
            $games = DB::table('games')->where('status', Game::STATUS_FINISHED)
                    ->whereMonth('created_at', $month)->get();

            return view('admin.statistics.index', ['games' => $games]);
        }

        return view('admin.statistics.index', ['games' => $games]);
    }
}
