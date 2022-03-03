<?php

namespace App\Http\Controllers\v1\Rest;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuthorizedUserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function update(Request $request) {
        $rules = [
            'first_name' => 'string|max:191',
            'last_name' => 'string|max:191',
            'login' => [
                'string',
                'min:3',
                'max:191',
                Rule::unique('users', 'login')->ignore(auth()->id()),
            ],
            'image' => 'image',
            'workplace' => 'string|max:50',
            'organization' => 'string|max:50',
            'country_id' => 'exists:countries,id',
            'city_id' => 'exists:cities,id',
            'password' => 'string|min:3|max:255'
        ];
        $this->validate($request, $rules);

        $user = auth()->user();

        $user->fill($request->only([
            'password', 'first_name', 'last_name', 'image', 'workplace',
            'organization', 'country_id', 'city_id', 'login'
        ]));

        $user->save();

        $user->refresh();

        return self::Response(200, new AuthorizedUserResource($user));
    }

    public function updatePhone() {
        //TODO Add update Phone logic
    }

}
