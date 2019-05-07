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

Route::get('/install','InsalesController@install');
Route::get('/uninstall','InsalesController@uninstall');
Route::get('/login','InsalesController@login');
Route::get('/autologin','InsalesController@autologin');

Route::get('/app','AppController@index');
Route::get('/info','AppController@info');
