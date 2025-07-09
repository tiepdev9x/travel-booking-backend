<?php

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


Route::group(['namespace' => '\Modules\Country\Http\Controllers\Api'], function () {

    /*
     *
     *  Frontend Quotes Routes
     *
     * ---------------------------------------------------------------------
     */
    $module_name = 'countries';
    $controller_name = 'CountriesController';
    Route::get("$module_name/list", ['as' => "$module_name.getCountries", 'uses' => "$controller_name@getCountries"]);
});
