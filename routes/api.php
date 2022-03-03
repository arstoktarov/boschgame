<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


//Route::get('/', function () {
//    event(new \App\Events\GameUpdatedEvent(User::find(7), \App\Models\Game::first()));
//});


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['namespace' => 'v1\Rest', 'prefix' => 'v1'], function() {


    Route::prefix('settings')->group(function() {
        Route::get('game_urls', 'SettingController@gameUrls');
    });

    Route::get('webviews','SettingController@webviews');

    Route::group(['prefix' => 'auth'], function () {

        Route::post('register', 'AuthController@register');
        Route::post('verify', 'AuthController@verifyCode');
        Route::post('create', 'AuthController@createUser');
        Route::post('login', 'AuthController@login');
        Route::post('logout', 'AuthController@logout');
        Route::post('refresh', 'AuthController@refresh');
        Route::get('me', 'AuthController@me');
	Route::post('delete', 'AuthController@deleteAccount');

    });

    Route::prefix('users')->group(function() {
        Route::get('/statistics', 'UserController@statistics')->middleware('auth:api');
        Route::get('{user}', 'UserController@show');
    });

    Route::prefix('countries')->group(function() {
        Route::get('/', 'CountryController@index');
        Route::get('/{country}', 'CountryController@show');
        Route::get('/{country}/cities', 'CountryController@cities');
        Route::get('/{country}/cities/{city}', 'CountryController@showCity');
    });

    Route::prefix('password/reset')->group(function() {

        Route::post('request', 'PasswordResetController@requestReset');
        Route::post('verifyCode', 'PasswordResetController@verifyCode');
        Route::post('newPassword', 'PasswordResetController@resetPassword');

    });

    Route::middleware('auth:api')->group(function() {

        Route::get('top', 'UserController@topList');
        Route::get('top/general', 'UserController@topListGeneral');
        Route::get('top/friends', 'UserController@topListFriends');
        Route::get('top/byCountry', 'UserController@topListByCountry');

        Route::post('profile', 'ProfileController@update');

        Route::group(['prefix' => 'friends'], function() {

            Route::get('/', 'FriendshipController@index');
            Route::post('/', 'FriendshipController@create');
            Route::get('/search', 'FriendshipController@search');
            Route::post('/{id}/add', 'FriendshipController@create');
            Route::get('/{id}', 'FriendshipController@show');
            Route::delete('/{id}', 'FriendshipController@destroy');

        });

        Route::group(['prefix' => 'blacklist'], function() {

            Route::get('/', 'BlacklistController@index');
            Route::post('/', 'BlacklistController@create');
            Route::post('/addMany', 'BlacklistController@addMany');
            Route::delete('/destroyMany', 'BlacklistController@destroyMany');
            Route::post('/{id}/add', 'BlacklistController@create');
            Route::get('/{id}', 'BlacklistController@show');
            Route::delete('/{id}', 'BlacklistController@destroy');

        });

        Route::group(['prefix' => 'questions'], function() {
            Route::post('suggestion', 'QuestionController@createSuggestion');
        });

        Route::group(['prefix' => 'game'], function() {

            Route::get('waiting', 'GameController@getWaitingGames');
            Route::get('myTurn', 'GameController@getMyTurnGames');
            Route::get('active', 'GameController@getActiveGames');
            Route::get('activeAll', 'GameController@getActiveAllGames');
            Route::get('finished', 'GameController@getFinishedGames');
            Route::post('random', 'GameController@randomGame');
            Route::get('{game_id}/result', 'GameController@pointZaLastGame');
            Route::post('withFriend', 'GameController@startGameWithFriend');
            Route::get('/{game}', 'GameController@getGame');
            Route::post('/{game}/round/start', 'GameController@startNewRound');
            Route::post('/{game}/surrender', 'GameController@surrenderGame');
            Route::post('round/{round}/answers/create', 'GameController@answerRound');
            Route::post('round/{round}/answers/incorrect', 'GameController@setIncorrectAnswers');
            //Route::post('round/{roundId}/questions', 'GameController@questions');
            //Route::get('round/{roundId}', 'GameController@getRound');
        });

        Route::group(['prefix' => 'categories'], function() {
            Route::get('/', 'CategoryController@index');
            Route::get('/random', 'CategoryController@random');
        });


    });



});
