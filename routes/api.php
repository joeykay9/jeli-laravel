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
	//Request new otp
	Route::get('/{customer}/otp', 'API\OtpController@requestOTP');
	//Verify otp
	Route::post('/{customer}/otp', 'API\OtpController@verifyOTP');
	Route::post('/{customer}/activate', 'API\CustomerController@activate');

	//Avatar
	Route::post('/{customer}/avatar', 'API\CustomerAvatarController@update');
	Route::delete('/{customer}/avatar', 'API\CustomerAvatarController@destroy');

	//Settings
	Route::patch('/{customer}/settings', 'API\SettingsController@update');
});

Route::group([
	'prefix' => 'moments'
], function() {

	Route::post('/{moment}/end', 'API\MomentController@end');
	Route::post('/{moment}/restore', 'API\MomentController@restore');
	
	Route::post('/{moment}/icon', 'API\MomentImageController@update');
	Route::delete('/{moment}/icon', 'API\MomentImageController@destroy');

	//Organisers
	Route::get('/{moment}/organisers', 'API\MomentOrganiserController@index');
		//View moment organisers in  Jelispace [You need to be an organiser to see that]
	Route::post('/{moment}/organisers', 'API\MomentOrganiserController@store'); //Add list of organisers [You need to be a moment admin to do that]
	Route::patch('/{moment}/organisers/{customer}', 'API\MomentOrganiserController@updateAdminStatus'); //Update admin status of organiser [You need to be a moment admin to do that]
	Route::delete('/{moment}/organisers/{customer}', 'API\MomentOrganiserController@removeOrganiser'); //Remove an organiser from a moment's Jelispace [You need to be a moment admin to do that]

	//Guests
	Route::get('/{moment}/guests', 'API\MomentGuestController@index'); //View moment guest list [You need to be an organiser to see that]
	Route::post('moments/{moment}/guests', 'API\MomentGuestController@store'); //Add list of guests [You need to be a moment admin to do that]
	Route::delete('moments/{moment}/guests/{customer}', 'API\MomentGuestController@removeGuest'); //Remove a customer from guest list [You need to be a moment admin to do that]

	//To do list
	Route::post('moments/{moment}/todos', 'API\TodoController@store'); //Create list items
});

Route::apiResources([
	'customers' => 'API\CustomerController',
	'moments' => 'API\MomentController'
]);

Route::apiResource('businesses', 'API\BusinessController')->only([
	'index', 'show'
]);
