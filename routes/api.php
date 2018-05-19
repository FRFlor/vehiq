<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('question/testAnswer', 'QuestionsController@testAnswer');


Route::post('/game/notifyTimeOut', 'GameController@notifyTimeOut');
Route::post('/game/answerQuestion', 'GameController@answerQuestion');
Route::get('/game/getCurrentQuestion', 'GameController@getCurrentQuestion');


Route::get('/game/secondsToGame', 'GameController@getSecondsToGame');