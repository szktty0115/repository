<?php

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

Route::get('/login', function () {
    return view('user.login');
});
Route::get('/top', function () {
    return view('user.top');
});
Route::get('/newlogin', function () {
    return view('user.newlogin');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::prefix('x')->name('x.')->group(function () {
    Route::get('/', 'XDashboardController@index')->name('dashboard');
    Route::post('/generate', 'XDashboardController@generate')->name('generate');
    Route::post('/posts', 'XDashboardController@storeManual')->name('posts.store');
    Route::post('/posts/{post}/schedule', 'XDashboardController@schedule')->name('posts.schedule');
    Route::post('/posts/{post}/publish', 'XDashboardController@publishNow')->name('posts.publish');
    Route::delete('/posts/{post}', 'XDashboardController@destroy')->name('posts.destroy');
    Route::post('/followers', 'XDashboardController@recordFollowers')->name('followers.store');
});
