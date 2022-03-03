<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\UserResource;
use App\Models\Car;
use App\Models\CarTravel;
use App\Models\CarTravelPlace;
use App\Models\City;
use App\Models\CommentDislike;
use App\Models\CommentLike;
use App\Models\Favorite;
use App\Models\Post;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller

{

    public function index(Request $request)
    {

        $user = User::leftJoin('countries','countries.id','users.country_id')
            ->leftJoin('cities','cities.id','users.city_id')
            ->select('users.*','countries.title as country','cities.title as city')
            ->orderBy('users.id','desc');
        if ($request['search']) {
            $user = $user->where(function ($query) use ($request) {
                $query->where('phone', 'LIKE', "%$request->search%")
                    ->orWhere('first_name', 'LIKE', "%$request->search%")
                    ->orWhere('last_name', 'LIKE', "%$request->search%");
            });
        }
        if ($request['city']) {
            $user = $user->where(function ($query) use ($request) {
                $query->where('city_id', $request['city']);
            });
        }

        $data['users']= $user->paginate(50);
        $data['search']= $request['search'];
        $data['cities'] = City::all();
        return view('admin.user.index', $data);
    }

    public function show($id)
    {
        $data['user'] = User::leftJoin('countries','countries.id','users.country_id')
            ->leftJoin('cities','cities.id','users.city_id')
            ->select('users.*','countries.title as country','cities.title as city')
            ->where('users.id',$id)
            ->first();
        $data['id'] = $id;

        return response()->view('admin.user.show',$data);
    }

    public function edit($id, Request $request)
    {
        $data['user'] = User::leftJoin('countries','countries.id','users.country_id')
            ->leftJoin('cities','cities.id','users.city_id')
            ->select('users.*','countries.title as country','cities.title as city')
            ->where('users.id',$id)
            ->first();

        return view('admin.user.edit',$data);

    }

    public function update($id,Request $request)
    {
        $rules = [
            'phone'=> 'required',
        ];
        $messages = [

        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return back()->withErrors($validator->errors());
        }

        $user = User::findOrFail($id);
        if ($request['first_name']){
            $user->first_name = $request['first_name'];
        }
        if ($request['last_name']){
            $user->last_name = $request['last_name'];
        }
        if ($request['phone']){
            if (User::where('id','<>',$user->id)->where('phone',$request['phone'])->exists()){
                return back()->withErrors('Телефон номер занять');
            }
            $user->phone = $request['phone'];
        }
        if ($request['login']){
            if (User::where('id','<>',$user->id)->where('login',$request['login'])->exists()){
                return back()->withErrors('login занять');
            }
            $user->login = $request['login'];
        }

        if ( $request['password_new']){
//            if (!Hash::check($request['password_old'],$user->password)){
//                return back()->withErrors('Неверный пароль');
//            }
            $user->password = bcrypt($request['password_new']);
        }
        if  ($request['workplace']){
            $user->workplace = $request['workplace'];
        }

        if  ($request['organization']){
            $user->organization = $request['organization'];
        }


        $user->save();
        return redirect()->route('admin.user.index');
    }

    public function destroy($id)
    {
        $l = User::findOrFail($id);
        $l->delete();
        return redirect()->back();
    }

    public function city(Request $request)
    {
        dd("city");
    }


}
