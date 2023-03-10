<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

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
Route::post('/saveApp', 'App\Http\Controllers\HomeController@saveApp')->name('saveApp');
Route::post('/checkExistingApp', 'App\Http\Controllers\HomeController@checkExistingApp')->name('checkExistingApp');
Route::get('/removeApp', 'App\Http\Controllers\HomeController@removeAppointment')->name('removeApp');
