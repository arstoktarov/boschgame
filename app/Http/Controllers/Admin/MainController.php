<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Http\Resources\CommentResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserHistoryResource;
use App\Http\Resources\UserResource;
use App\Models\Admin;
use App\Models\HistoryHiddenFrom;
use App\Models\History;
use App\Models\HistoryHiddenUser;
use App\Models\HistoryShow;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;

use Carbon\Carbon;
use Faker\Provider\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MainController extends Controller
{

    function login(Request $request){

        if (session()->has('admin')){
            return redirect()->route('admin.main');
        }

        if ($request->getMethod() == 'GET'){
            return view('admin.login');
        }else{

            $rules = [
                'login'=> 'required|exists:admins,login',
                'password'=> 'required',
            ];
            $messages = [
                'login.exists' => 'Неверный логин или пароль'
            ];

            $validator = Validator::make($request->all(), $rules,$messages );
            if ($validator->fails()){
                return back()->withErrors($validator->errors()->first());
            }


            $admin = Admin::whereLogin($request['login'])->first();
            if (!Hash::check($request['password'],$admin->password)){
                return back()->withErrors('Неверный логин или паролssь');
            }
            session()->put('admin',1);
            session()->save();
            return redirect()->route('admin.main');
        }

    }

    function main(){
        return view('admin.main');
    }

    function out(){
        session()->forget('admin');
        return redirect()->route('admin.login');
    }

    function setting(Request $request){
        if ($request->getMethod() == 'GET'){
            $data['setting'] = Setting::first();
            return view('admin.setting',$data);
        }

        $s = Setting::first();

        $s->terms_of_use = $request['terms_of_use'];
        $s->privacy_policy = $request['privacy_policy'];
        $s->contact = $request['contact'];
        $s->save();

        return redirect()->back()->with('success','Сохранено');
    }
}
