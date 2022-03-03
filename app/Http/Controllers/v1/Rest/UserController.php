<?php

namespace App\Http\Controllers\v1\Rest;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Country;
use App\Models\Game;
use App\Models\User;
use App\Services\UserService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{

    public function show(User $user)
    {
        return self::Response(200, UserResource::make($user));
    }

    public function topList()
    {
        $users = User::orderByDesc('scores')->limit(User::TOP_LIST_USERS_COUNT)->get();
        return self::Response(200, $users);
    }

    public function topListGeneral()
    {

        $users = User::orderByDesc('scores')
            ->withoutDefaultPlayer()
            ->limit(User::TOP_LIST_USERS_COUNT)
            ->get();

        $allUsers = User::orderByDesc('scores')
            ->withoutDefaultPlayer()
            ->get();
        $current = auth()->user();
        $me = $current;
        $position = 0;
        foreach ($allUsers as $idx => $user) {
            if ($idx < 20 && $user == $current) {
                continue;
            }else if ($user==$current){
                $position = $idx;
            }
        }


        $data = [
            'id' => $me['id'],
            'first_name' => $me['first_name'],
            'last_name' => $me['last_name'],
            'login' => $me['login'],
            'image' => $me['image'],
            'rating' => $me['rating'],
            'scores' => $me['scores'],
            'in_blacklist' => $me['in_blacklist'],
            'in_friends' => $me['in_friends'],
            'position' => $position
        ];

        return self::Response(200, ['users' => UserResource::collection($users), 'current_user' => $data]);
    }

    public function topListFriends()
    {
        $users = auth()->user()
            ->friends()
            ->withoutDefaultPlayer()
            ->orderByDesc('scores')
            ->limit(User::TOP_LIST_USERS_COUNT)
            ->get();

        return self::Response(200, UserResource::collection($users));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function topListByCountry(Request $request)
    {
        $rules = [
            'country_id' => 'required',
            'city_id' => 'nullable'
        ];
        $this->validate($request, $rules);

        $country = Country::findOrFail($request['country_id']);

        if ($request['city_id']) {
            $city = $country->cities()->findOrFail($request['city_id']);
            $users = $city->users()
                ->withoutDefaultPlayer()
                ->orderByDesc('scores')
                ->limit(User::TOP_LIST_USERS_COUNT)
                ->get();
        } else {
            $users = $country->users()
                ->withoutDefaultPlayer()
                ->orderByDesc('scores')
                ->limit(User::TOP_LIST_USERS_COUNT)
                ->get();
        }

        return self::Response(200, UserResource::collection($users));
    }

    /**
     * @param Request $request
     * @param UserService $userService
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function statistics(Request $request, UserService $userService)
    {
        $rules = [
            'user_id' => 'numeric'
        ];
        $this->validate($request, $rules);
        $user = isset($request['user_id']) ? User::findOrFail($request['user_id']) : auth()->user();

        $finished_games = $user->games()->where('status', Game::STATUS_FINISHED);

        $finished_games_count = $finished_games->count();
        
        $wins_count = (clone $finished_games)
            ->where('winner_id', $user->id)
            ->count();

        $draws_count = (clone $finished_games)
            ->where('finish_status', Game::FINISH_STATUS_DRAW)
            ->count();

        $loses_count = (clone $finished_games)
            ->where('winner_id', '!=', $user->id)
            ->count();

        $response = [
            'stats' => [
                'finished_games_count' => $finished_games_count,
                'draws_count' => $draws_count,
                'wins_count' => $wins_count,
                'loses_count' => $loses_count,
                'wins_percentage' =>
                    $finished_games_count > 0
                        ? $this->calculatePercentage($wins_count, $finished_games_count)
                        : 0,
                'draws_percentage' =>
                    $finished_games_count > 0
                        ? $this->calculatePercentage($draws_count, $finished_games_count)
                        : 0,
                'loses_percentage' =>
                    $finished_games_count > 0
                        ? $this->calculatePercentage($loses_count, $finished_games_count)
                        : 0,
                'points_average' => $user->getPointsAvg(),
                'rating' => $user->getRowNumber(),
            ],
            'category_stats' => $userService->getCategoryStatistics($user)
        ];

        return self::Response(200, $response);

    }

    public function calculatePercentage($value, $maxValue)
    {
        try {
            return (($value / $maxValue) * 100);
        } catch (Exception $e) {
            Log::error("Couldn't calculate percentage: \n" . $e->getMessage());
            return 0;
        }
    }

}
