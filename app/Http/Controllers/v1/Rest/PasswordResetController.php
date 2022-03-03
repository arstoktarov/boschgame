<?php

namespace App\Http\Controllers\v1\Rest;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Packages\SMS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function requestReset(Request $request) {
        $rules = [
            'phone' => 'required|exists:users'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) return self::Response(400, null, $validator->errors()->first());

        $user = User::where('phone', $request['phone'])->first();
        if (!$user) return self::Response(404, null, 'User not found');


        $code = rand(1000,9999);
        Cache::put($user->phone.'_code', $code, 120);
        SMS::send($request['phone'], "Код подтверждения : $code");


        return self::Response(200, $user->only('phone'));
    }

    public function verifyCode(Request $request) {
        $rules = [
            'phone' => 'required|exists:users',
            'code' => 'required|digits:4'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) return self::Response(400, null, $validator->errors()->first());

        $user = User::where('phone', $request['phone'])->first();
        if (!$user) return self::Response(404, null, 'User not found');

        $code = Cache::get($user->phone.'_code');
        if (!$code) return self::Response(404, null, 'Code not found');

        if ($request['code'] != $code) return self::Response(400, null, 'Code is incorrect');

        $token = Str::random(30);
        Cache::put($user->phone.'_token', $token, 120);

        return self::Response(200, [
            'phone' => $user->phone,
            'reset_token' => $token
        ]);
    }

    public function resetPassword(Request $request) {
        $rules = [
            'phone' => 'required',
            'reset_token' => 'required',
            'new_password' => 'required'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) return self::Response(400, null, $validator->errors()->first());

        $user = User::where('phone', $request['phone'])->first();
        if (!$user) return self::Response(404, null, 'User not found');

        $token = Cache::get($user->phone.'_token');
        if (!$token) return self::Response(404, null, 'Code not found');

        $user->password = $request['new_password'];
        $user->save();


        $token = auth()->login($user);

        return $this->respondWithToken($token);
    }


}
