<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/socket', function () {
    return view('socket');
});



Route::get('/ws_socket', function () {
    return view('ws_socket');
});




Route::name('admin.')->namespace('Admin')->group(function () {
    Route::any('admin', 'MainController@login')->name("login");
    Route::group(['prefix' => 'admin', 'middleware' => 'adminCheck'], function () {
        Route::get('main', 'MainController@main')->name("main");
        Route::get('out', 'MainController@out')->name("out");
        Route::get('webviews', 'MainController@webviews')->name("webviews");


        Route::name('user.')->prefix('user')->group(function (){
            Route::get('index', 'UserController@index')->name('index');
            Route::get('show/{id}', 'UserController@show')->name('show');
            Route::get('edit/{id}', 'UserController@edit')->name('edit');
            Route::post('update/{id}', 'UserController@update')->name('update');
            Route::get('destroy/{id}', 'UserController@destroy')->name('destroy');
        });


        Route::name('category.')->prefix('category')->group(function (){
            Route::get('index', 'CategoryController@index')->name('index');
            Route::get('add', 'CategoryController@add')->name('add');
            Route::post('create', 'CategoryController@create')->name('create');
            Route::get('edit/{id}', 'CategoryController@edit')->name('edit');
            Route::post('update/{id}', 'CategoryController@update')->name('update');
            Route::get('destroy/{id}', 'CategoryController@destroy')->name('destroy');
            Route::get('games/{id}', 'CategoryController@games')->name('games');
            Route::get('game/{id}', 'CategoryController@getGame')->name('game');
        });

        Route::name('question.')->prefix('question')->group(function (){
            Route::get('index/{id}', 'QuestionController@index')->name('index');

            Route::get('add/{id}', 'QuestionController@add')->name('add');
            Route::post('create', 'QuestionController@create')->name('create');

            Route::get('show/{id}', 'QuestionController@show')->name('show');
            Route::get('edit/{id}', 'QuestionController@edit')->name('edit');
            Route::post('update/{id}', 'QuestionController@update')->name('update');
            Route::get('destroy/{id}', 'QuestionController@destroy')->name('destroy');
        });

        Route::name('statistics.')->prefix('statistics')->group(function () {
            Route::get('index', 'CategoryController@statisticsIndex')->name('index');
            Route::get('get/games', 'CategoryController@getByMonth')->name('month');
        });
    });
});
