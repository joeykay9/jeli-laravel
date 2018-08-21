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

// Route::get('send_test_email', function(){
// 	Mail::raw('Sending emails with Mailgun and Laravel is easy!', function($message)
// 	{
// 		$message->subject('Mailgun and Laravel are awesome!');
// 		$message->to('joeykay9@gmail.com');
// 	});
// });

Route::group([

    'prefix' => 'auth'

], function ($router) {

    Route::post('login', 'API\AuthController@login');
    Route::post('logout', 'API\AuthController@logout');
    Route::post('refresh', 'API\AuthController@refresh');
    Route::post('me', 'API\AuthController@me');
    Route::post('forgot_password', 'API\AuthController@forgotPassword');
    Route::post('reset_password', 'API\AuthController@resetPassword');
});

Route::group([

	'prefix' => 'customers'

], function () {
	//Return all customers
	Route::middleware('auth:api')->get('/', 'API\CustomerController@index');
	//Register a customer
	Route::post('/', 'API\CustomerController@store');
	//Will use this for viewing a contact's Jeli details
	Route::middleware('auth:api')->get('/{customer}', 'API\CustomerController@show');
	//Update Jeli Customer details
	Route::middleware('auth:api')->patch('/{customer}', 'API\CustomerController@update');
	//Delete Jeli Customer Details
	Route::middleware('auth:api')->delete('/{customer}', 'API\CustomerController@destroy');
	//Request new otp
	Route::get('/{customer}/otp', 'API\OtpController@requestOTP');
	//Verify otp
	Route::post('/{customer}/otp', 'API\OtpController@verifyOTP');
	Route::middleware('auth:api')->post('/{customer}/activate', 'API\CustomerController@activate');
	Route::middleware('auth:api')->post('/{customer}/avatar', 'API\CustomerAvatarController@update');
	Route::middleware('auth:api')->delete('/{customer}/avatar', 'API\CustomerAvatarController@destroy');
	Route::middleware('auth:api')->patch('/{customer}/settings', 'API\SettingsController@update');
});

Route::group([
	
	'middleware' => 'auth:api'

], function() {
	//Moment
	Route::get('moments', 'API\MomentController@index'); //Get all moments a user belongs to
	Route::post('moments', 'API\MomentController@store'); //Store a moment
	Route::get('moments/{moment}', 'API\MomentController@show'); //Show a specfic moment
	Route::middleware('moment.creator')
		->put('moments/{moment}', 'API\MomentController@update'); //Update a specfic moment
	Route::middleware('moment.creator')
		->delete('moments/{moment}', 'API\MomentController@destroy'); //Delete a specfic moment
	//HTML forms do not support PUT, PATCH, or DELETE actions
	Route::middleware('moment.creator')->post('/{moment}/avatar', 'API\MomentImageController@update');
	Route::middleware('moment.creator')->delete('/{moment}/avatar', 'API\MomentImageController@destroy');

	//Organisers
	Route::middleware('moment.organiser')
		->get('moments/{moment}/organisers', 'API\MomentOrganiserController@index');
		//View moment organisers in  Jelispace [You need to be an organiser to see that]
	Route::middleware('moment.admin')
		->post('moments/{moment}/organisers', 'API\MomentOrganiserController@store'); //Add list of organisers [You need to be a moment admin to do that]
	Route::middleware('moment.admin')
		->patch('moments/{moment}/organisers/{customer}', 'API\MomentOrganiserController@updateAdminStatus'); //Update admin status of organiser [You need to be a moment admin to do that]
	Route::middleware('moment.admin')
		->delete('moments/{moment}/organisers/{customer}', 'API\MomentOrganiserController@removeOrganiser'); //Remove an organiser from a moment's Jelispace [You need to be a moment admin to do that]

	//Guests
	Route::middleware('moment.organiser')
		->get('moments/{moment}/guests', 'API\MomentGuestController@index'); //View moment guest list [You need to be an organiser to see that]
	Route::middleware('moment.admin')
		->post('moments/{moment}/guests', 'API\MomentGuestController@store'); //Add list of guests [You need to be a moment admin to do that]
	Route::middleware('moment.admin')
		->delete('moments/{moment}/guests/{customer}', 'API\MomentGuestController@removeGuest'); //Remove a customer from guest list [You need to be a moment admin to do that]

	//To do list
	Route::middleware('moment.admin')
		->post('moments/{moment}/todos', 'API\TodoController@store'); //Create list items

	//Return all businesses
	Route::get('businesses', 'API\BusinessController@index');
	Route::get('businesses/{business}', 'API\BusinessController@show');
});
