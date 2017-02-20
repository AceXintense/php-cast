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
Route::get('/getShuffle', 'RequestController@getShuffle');
Route::post('/toggleShuffle', 'RequestController@toggleShuffle');

//Toggles play through of the Queue and plays one song after another.
Route::get('/getPlayThrough', 'RequestController@getPlayThrough');
Route::post('/togglePlayThrough', 'RequestController@togglePlayThrough');

//Toggles play through of the Queue and plays one song after another.
Route::get('/getPlayThroughDirection', 'RequestController@getPlayThroughDirection');
Route::post('/togglePlayThroughDirection', 'RequestController@togglePlayThroughDirection');

//Gets and sets the paused record in the database.
Route::get('/isPaused', 'RequestController@isPaused'); //Check to see if there is a paused file in the database.
Route::post('/setPaused', 'RequestController@setPaused');

//Plays the requested file.
Route::post('/playFile', 'RequestController@playFile');
Route::post('/stopFile', 'RequestController@stopFile');

//Handles removing of files from the database and the filesystem.
Route::post('/clearQueue', 'RequestController@clearQueue');
Route::post('/removeFile', 'RequestController@removeFile');

Route::post('/isQueueDifferent', 'RequestController@isQueueDifferent');

//Adds the requested URL to the database and also downloads the file.
Route::post('/addRequest', 'RequestController@addRequest');

//Skip to the next song in the queue.
Route::put('/skipToNext', 'RequestController@skipToNext');
Route::put('/skipToPrevious', 'RequestController@skipToPrevious'); //Skip to the previous song in the queue.

//Changes the volume on the driver on the server.
Route::get('/getVolume', 'RequestController@getVolume');
Route::post('/setVolume', 'RequestController@setVolume');

//Resets all PHP and the Database back to default.
Route::get('/resetEnvironment', 'RequestController@resetEnvironment');

//Toggles shuffling of the Queue and plays one song after another.
Route::get('/getChartData', 'RequestController@getChartData');

//Shows the phpinfo for developer usage.
Route::get('/phpInfo', 'RequestController@phpInfo');