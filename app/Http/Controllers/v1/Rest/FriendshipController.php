<?php

namespace App\Http\Controllers\v1\Rest;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserFriend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FriendshipController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api')->except('search');
    }

    public function index(Request $request) {
        $user = auth()->user();
        $friends = $user->friends()->orderBy('first_name')->orderBy('last_name')->paginate(self::PAGINATION_COUNT);
        return self::Response(200, $friends);
    }

    public function search(Request $request) {
        $rules = [
            'text' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) return self::Response(400, null, $validator->errors()->first());

        $textSql = '%'.$request['text'].'%';

        $friends = User::
            where('login', $request['text'])
            ->orWhere('phone', $request['text'])
            ->orWhere('first_name', 'like', $textSql)
            ->orWhere('last_name', 'like', $textSql)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->paginate(self::PAGINATION_COUNT);

        return self::Response(200, $friends);
    }

    public function show($id, Request $request) {
        $user = auth()->user();

        $friend = $user->friends()->find($id);
        if (!$friend) return self::Response(404, null, 'Friend not found');

        return self::Response(200, $friend);
    }

    public function create($id, Request $request) {
        $user = User::findOrFail($id);

        if ($user->id == auth()->id()) return self::errorResponse('Вы не можете добавлять в друзья самого себя', 400);

        auth()->user()->friends()->syncWithoutDetaching($user->id);
        return self::Response(200, $user->refresh());
    }

    public function destroy($id, Request $request) {
        $friend = auth()->user()->friends()->find($id);
        if (!$friend) return self::Response(404, null, 'Friend not found');
        auth()->user()->friends()->detach($id);
        return self::Response(200, $friend);
    }

}
