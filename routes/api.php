<?php

use Illuminate\Http\Request;
use App\Business;
use App\Customer;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


//Return all customers
Route::get('customers', 'APICustomerController@index');
//Will use this for logging in or for viewing a contact's Jeli details
Route::get('customers/{customer}', 'APICustomerController@show');
//Update Jeli Customer details
Route::put('customers/{customer}', 'APICustomerController@update');
//Register Jeli Customer
Route::post('register', 'Auth\RegisterController@register');
//Login Jeli Customer
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout');


Route::group(['middleware' => 'auth:api'], function() {
	//Return all businesses
	Route::get('businesses', 'APIBusinessController@index');
	Route::get('businesses/{business}', 'APIBusinessController@show');
});
