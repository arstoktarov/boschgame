<?php

namespace App\Http\Controllers\v1\Rest;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuthorizedUserResource;
use App\Models\User;
use App\Packages\SMS;
use App\Services\RegisterService;
use App\Services\ResponseService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\JWT;

class AuthController extends Controller
{

	public function deleteAccount()
    {
        $userId = auth()->id();

        $user = User::where('id', $userId)->update([
            'phone' => "deleted " . rand().rand().rand().rand(),
        ]);

        auth()->logout();
        return self::Response(200, null, 'Successfully delete');
    }

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'verifyCode', 'createUser']]);
    }

    public function register(Request $request) {
        $rules = [
            'phone' => 'required|unique:users',
            'device_token' => '',
            'device_type' => ''
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails())
            return self::Response(400, null, $validator->errors()->first());

        if (Cache::has($request['phone'].'_code'))
            return self::Response(400, null, 'Please wait until last sent code expired');

        $code = rand(1000,9999);
        SMS::send($request['phone'], "Код подтверждения номера телефона: $code");

        Cache::put($request['phone'].'_code', $code, 30);

        return self::Response(200, $request->only('phone'));
    }

    public function verifyCode(Request $request) {
        $rules = [
            'phone' => 'required',
            'code' => 'required'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) return self::Response(400, null, $validator->errors()->first());

        $phone = $request['phone'];
        if (!Cache::has($phone.'_code')) return self::Response(400, null, 'Code time expired');

        $code = Cache::get($phone.'_code');
        if ($request['code'] != $code) return self::Response(400, null, 'Code is incorrect');


        Cache::forget($phone.'code');
        $token = Str::random(30);
        Cache::put($phone.'_token', $token);
        if (!$token) return self::Response(500, null, 'Cannot create token');

        return self::Response(200, ['registration_token' => $token]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function createUser(Request $request) {
        $rules = [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'login' => 'required|unique:users,login',
            'phone' => 'required',
            'image' => 'image|size:20000',
            'registration_token' => '',
            'password' => 'required',
            'country_id' => 'exists:countries,id',
            'city_id' => 'exists:cities,id',
            'device_token' => 'required',
            'device_type' => 'required',
        ];

        $this->validate($request, $rules);

        $phone = $request['phone'];
        if (!Cache::has($phone.'_token')) return self::Response(400, 'Token has expired');
        $token = Cache::get($phone.'_token');
        if ($request['registration_token'] != $token) return self::Response(400,'Token is incorrect');

        Cache::forget($phone.'_token');

        $user = new User();

        $user->fill($request->only([
            'login', 'first_name', 'last_name', 'phone', 'password',
            'image', 'workplace', 'organization', 'country_id', 'city_id',
            'device_token', 'device_type'
        ]));

        //TODO add country_id, city_id fields

        $user->setAttribute('phone_verified_at', Carbon::now());

        $user->save();

        $user->refresh();

        $token = auth()->login($user);

        return $this->respondWithToken($token);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $rules = [
            'phone' => 'required|string',
            'password' => 'required|min:3',
	    //'device_token' => 'required',
            //'device_type' => 'required',
        ];
        $this->validate($request, $rules);

        $request['phone'] = str_replace(['(', ')', '+', ' ', '-'], '', $request['phone']);

        $credentials = [
            'phone' => $request['phone'],
            'password' => $request['password'],
        ];
        
        if (! $token = auth()->attempt($credentials)) {
            return self::Response(401, null);
        }

	if($request->get('device_token') && $request->get('device_type')) {
	 //g code
        	User::where('phone', $request->get('phone'))->update([
            		'device_token' => $request->get('device_token'),
            		'device_type' => $request->get('device_type'),
        	]);

	}
        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        if (!auth()->check()) return self::Response(401, null);

        return self::Response(200, new AuthorizedUserResource(auth()->user()));
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return self::Response(200, null, 'Successfully logged out');
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {

	if(boolval(strpos(auth()->user()->phone, 'deleted'))) {
            return self::Response(200, null, 'Successfully logged out');
        }
        return $this->respondWithToken(auth()->refresh());
    }



}
