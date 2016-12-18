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
//Gets the data to be displayed on the front-end.
Route::get('/getRequestedURLs', 'RequestController@getRequestedURLs');
Route::get('/getPlaying', 'RequestController@getPlaying');

//Plays the requested file.
Route::get('/playFile', 'RequestController@playFile');

//Handles removing of files from the database and the filesystem.
Route::get('/clearQueue', 'RequestController@clearQueue');
Route::get('/removeFile', 'RequestController@removeFile');

//Adds the requested URL to the database and also downloads the file.
Route::post('/addRequest', 'RequestController@addRequest');

//Changes the volume on the driver on the server.
Route::post('/changeVolume', 'RequestController@changeVolume');
