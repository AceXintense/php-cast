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

//Toggles shuffling of the Queue and plays one song after another.
Route::post('/setShuffle', 'RequestController@setShuffle');
Route::get('/getShuffle', 'RequestController@getShuffle');

//Gets and sets the paused record in the database.
Route::post('/setPaused', 'RequestController@setPaused');
//Check to see if there is a paused file in the database.
Route::get('/isPaused', 'RequestController@isPaused');

//Plays the requested file.
Route::post('/playFile', 'RequestController@playFile');
Route::get('/stopFile', 'RequestController@stopFile');

//Handles removing of files from the database and the filesystem.
Route::get('/clearQueue', 'RequestController@clearQueue');
Route::get('/removeFile', 'RequestController@removeFile');

//Adds the requested URL to the database and also downloads the file.
Route::post('/addRequest', 'RequestController@addRequest');

//Skip to the next song in the queue.
Route::put('/skipToNext', 'RequestController@skipToNext');
//Skip to the previous song in the queue.
Route::put('/skipToPrevious', 'RequestController@skipToPrevious');

//Changes the volume on the driver on the server.
Route::post('/setVolume', 'RequestController@setVolume');
Route::get('/getVolume', 'RequestController@getVolume');
