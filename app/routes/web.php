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

Route::prefix('threads')->name('threads.')->group(function () {
    Route::get('/', 'ThreadsDashboardController@index')->name('dashboard');
    Route::post('/generate', 'ThreadsDashboardController@generate')->name('generate');
    Route::post('/posts', 'ThreadsDashboardController@storeManual')->name('posts.store');
    Route::post('/posts/{post}/schedule', 'ThreadsDashboardController@schedule')->name('posts.schedule');
    Route::post('/posts/{post}/publish', 'ThreadsDashboardController@publishNow')->name('posts.publish');
    Route::delete('/posts/{post}', 'ThreadsDashboardController@destroy')->name('posts.destroy');
    Route::post('/followers', 'ThreadsDashboardController@recordFollowers')->name('followers.store');
});

Route::prefix('note')->name('note.')->group(function () {
    Route::get('/', 'NoteDashboardController@index')->name('dashboard');
    Route::post('/generate', 'NoteDashboardController@generate')->name('generate');
    Route::post('/articles/{article}', 'NoteDashboardController@update')->name('articles.update');
    Route::post('/articles/{article}/publish', 'NoteDashboardController@markPublished')->name('articles.publish');
    Route::post('/articles/{article}/draft', 'NoteDashboardController@markDraft')->name('articles.draft');
    Route::delete('/articles/{article}', 'NoteDashboardController@destroy')->name('articles.destroy');
});
