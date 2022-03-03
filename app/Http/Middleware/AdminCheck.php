<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use App\Models\User;

class AdminCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (session()->has('admin')){
            return $next($request);
        }else{
            return redirect()->route('admin.login')->withErrors('Нет доступа, введите логин и пароль');
        }
    }
}
