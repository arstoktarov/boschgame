<?php

namespace App\Providers;

use App\Models\Game;
use App\Models\User;
use App\Observers\GameObserver;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        User::observe(UserObserver::class);
        Game::observe(GameObserver::class);
        Schema::defaultStringLength(191);
    }
}
