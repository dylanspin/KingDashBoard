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
Route::post('health/check', 'API\SettingsController@health_check');
Route::post('login', 'API\LoginController@verify_user');
Route::group(['middleware' => 'auth:api'], function() {
    Route::post('verify/booking', 'API\BookingController@verify_booking');
});
//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});
