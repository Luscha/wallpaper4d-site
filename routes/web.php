<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index');
Route::get('/ajax/get-session-state', 'w4dSessionController@GetSessionState');
Route::get('/ajax/get-wallpaper', 'w4dWallpaperController@GetWallpaper');

// Images
Route::get('/vi/{iid}/{quality}.jpg', 'ImageController@thumbnail_jpeg')
	->where(['iid' => '[0-9A-Za-z_-]+', 'quality' => '(|mq|hq|sd|maxres)default']);
Route::get('/vi_webp/{iid}/{quality}.webp', 'ImageController@thumbnail_webp')
	->where(['iid' => '[0-9A-Za-z_-]+']);
	
Route::get('/i/{iid}={opt?}', 'ImageController@jpeg')
	->where(['iid' => '[0-9A-Za-z_-]+', 'opt' => '[0-9A-Za-z_\-,=]+']);
