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
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

//Route::get('/h2h/retail', 'RetailController@index');
//Route::post('/h2h/retail', 'RetailController@store');
//Route::post('/h2h/retail/status', 'RetailController@status');

Route::post('grosir', 'GrosirController@store');
Route::post('grosir/status', 'GrosirController@show');

//Route::post('grosir', 'GrosirControllerTest@testCase');
Route::post('grosir/callback', 'GrosirControllerTest@callback');