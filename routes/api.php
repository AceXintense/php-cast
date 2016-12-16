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
| /api/function
|
*/
Route::get('/getRequestedURLs', 'RequestController@getRequestedURLs');
Route::get('/getPlaying', 'RequestController@getPlaying');
Route::get('/removeRequest', 'RequestController@removeRequest');

Route::get('/playFile', 'RequestController@playFile');

Route::post('/addRequest', 'RequestController@addRequest');
