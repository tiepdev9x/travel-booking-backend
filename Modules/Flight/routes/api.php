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


Route::group(['namespace' => '\Modules\Flight\Http\Controllers\Api'], function () {

    /*
     *
     *  Frontend Quotes Routes
     *
     * ---------------------------------------------------------------------
     */
    $module_name = 'flight';
    $controller_name = 'FlightController';
    Route::get("$module_name/list", ['as' => "$module_name.getFlight", 'uses' => "$controller_name@getFlight"]);
    Route::get("$module_name/getPrice", ['as' => "$module_name.getPrice", 'uses' => "$controller_name@getPrice"]);
    Route::get("$module_name/getFightDetail", ['as' => "$module_name.getFightDetail", 'uses' => "$controller_name@getFightDetail"]);
    Route::post("$module_name/bookingChooseFlight", ['as' => "$module_name.getFightDetail", 'uses' => "$controller_name@bookingChooseFlight"]);
});
