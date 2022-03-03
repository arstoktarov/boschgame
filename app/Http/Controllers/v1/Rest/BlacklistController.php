<?php

namespace App\Http\Controllers\v1\Rest;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BlacklistController extends Controller
{
    public function index(Request $request) {
        $user = auth()->user();
        $friends = $user->blacklistedUsers()->orderBy('first_name')->orderBy('last_name')->paginate(self::PAGINATION_COUNT);
        return self::Response(200, $friends);
    }

    public function show($id, Request $request) {
        $user = auth()->user();

        $friend = $user->blacklistedUsers()->find($id);
        if (!$friend) return self::Response(404, null, 'Friend not found');

        return self::Response(200, $friend);
    }

    public function create($id, Request $request) {

        if (!User::where('id', $id)->exists())
            return self::Response(404, null, 'User not found');

        auth()->user()->blacklistedUsers()->syncWithoutDetaching($id);
        $friend = auth()->user()->blacklistedUsers()->find($id);
        return self::Response(200, $friend);

    }

    public function addMany(Request $request) {

        if (is_array($request->all())) {
            foreach ($request->all() as $userId) {
                if (is_numeric($userId) && User::where('id', $userId)->exists()) {
                    auth()->user()->blacklistedUsers()->syncWithoutDetaching($userId);
                }
            }
        }

        return self::Response(200, auth()->user()->blacklistedUsers);
    }

    public function destroyMany(Request $request) {

        if (is_array($request->all()))
        {
            auth()->user()->blacklistedUsers()->detach($request->all());
        }

        return self::Response(200, auth()->user()->blacklistedUsers);
    }

    public function destroy($id, Request $request) {
        auth()->user()->blacklistedUsers()->detach($id);
        return self::Response(200, null);
    }
}
