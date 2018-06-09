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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('send_test_email', function(){
	Mail::raw('Sending emails with Mailgun and Laravel is easy!', function($message)
	{
		$message->subject('Mailgun and Laravel are awesome!');
		$message->to('joeykay9@gmail.com');
	});
});

Route::group([

    'prefix' => 'auth'

], function ($router) {

    Route::post('login', 'APIAuthController@login');
    Route::post('logout', 'APIAuthController@logout');
    Route::post('refresh', 'APIAuthController@refresh');
    Route::post('me', 'APIAuthController@me');
    Route::post('forgot_password', 'APIAuthController@forgotPassword');
    Route::post('reset_password', 'APIAuthController@resetPassword');
});

Route::group([

	'prefix' => 'customers'

], function () {
	//Return all customers
	Route::middleware('auth:api')->get('/', 'APICustomerController@index');
	//Register a customer
	Route::post('/', 'APICustomerController@store');
	//Will use this for viewing a contact's Jeli details
	Route::middleware('auth:api')->get('/{customer}', 'APICustomerController@show');
	//Update Jeli Customer details
	Route::middleware('auth:api')->patch('/{customer}', 'APICustomerController@update');
	Route::get('/{customer}/otp', 'OtpController@requestNewOTP');
	Route::post('/{customer}/otp', 'OtpController@verifyOTP');
	Route::middleware('auth:api')->post('/{customer}/activate', 'APICustomerController@activate');
	Route::middleware('auth:api')->post('/{customer}/avatar', 'CustomerAvatarController@update');
});

Route::group([
	
	'middleware' => 'auth:api'

], function() {
	//Moment
	Route::get('moments', 'MomentController@index'); //Get all moments a user belongs to
	Route::post('moments', 'MomentController@store'); //Store a moment
	Route::get('moments/{moment}', 'MomentController@show'); //Show a specfic moment
	Route::put('moments/{moment}', 'MomentController@update'); //Update a specfic moment
	Route::delete('moments/{moment}', 'MomentController@destroy'); //Delete a specfic moment
	//HTML forms do not support PUT, PATCH, or DELETE actions

	//Return all businesses
	Route::get('businesses', 'APIBusinessController@index');
	Route::get('businesses/{business}', 'APIBusinessController@show');
});
