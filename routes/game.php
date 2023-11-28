<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
 
Route::group(['prefix'=>'game', 'as'=> 'game'], function () {
    Route::get('', 'API\Game\GameController@index')->name('index');
    Route::get('categories', 'API\Game\GameController@categories')->name('categories');
});