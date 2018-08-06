<?php

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

Route::get('/faqs', 'HomeController@faqs')->name('faqs');

Auth::routes();
Route::post('/logout', 'Auth\LoginController@customerLogout')->name('customerLogout');
Route::get('/home', 'HomeController@index')->name('home');

Route::prefix('business')->group(function() {
	Route::get('/login', 'Auth\BusinessLoginController@showLoginForm')->name('business.login');
	Route::post('/login', 'Auth\BusinessLoginController@login')->name('business.login.submit');
	Route::post('/logout', 'Auth\BusinessLoginController@logout')->name('business.logout');
	Route::get('/register', 'Auth\BusinessRegisterController@showRegistrationForm')->name('business.register');
	Route::post('/register', 'Auth\BusinessRegisterController@register')->name('business.register.submit');
	Route::get('/home', 'BusinessHomeController@index')->name('business.home');
	Route::post('/vendors/register', 'BusinessHomeController@registerVendor')->name('vendor.register.submit');
	Route::get('/password/reset', 'Auth\BusinessForgotPasswordController@showLinkRequestForm')->name('business.password.request');
	Route::post('/password/email', 'Auth\BusinessForgotPasswordController@sendResetLinkEmail')->name('business.password.email');
	Route::post('/password/reset', 'Auth\BusinessResetPasswordController@reset');
	Route::get('/password/reset/{token}', 'Auth\BusinessResetPasswordController@showResetForm')->name('business.password.reset');
});

Route::prefix('vendor')->group(function() {
	Route::get('/login', 'Auth\VendorLoginController@showLoginForm')->name('vendor.login');
	Route::post('/login', 'Auth\VendorLoginController@login')->name('vendor.login.submit');
	Route::post('/logout', 'Auth\VendorLoginController@logout')->name('vendor.logout');
	Route::get('/home', 'VendorHomeController@index')->name('vendor.home');
	Route::get('/password/reset', 'Auth\VendorForgotPasswordController@showLinkRequestForm')->name('vendor.password.request');
	Route::post('/password/email', 'Auth\VendorForgotPasswordController@sendResetLinkEmail')->name('vendor.password.email');
	Route::post('/password/reset', 'Auth\VendorResetPasswordController@reset');
	Route::get('/password/reset/{token}', 'Auth\VendorResetPasswordController@showResetForm')->name('vendor.password.reset');
});

Route::prefix('admin')->group(function() {
	Route::get('/login', 'Auth\AdminLoginController@showLoginForm')->name('admin.login');
	Route::post('/login', 'Auth\AdminLoginController@login')->name('admin.login.submit');
	Route::post('/logout', 'Auth\AdminLoginController@logout')->name('admin.logout');
	Route::get('/home', 'AdminHomeController@index')->name('admin.home');
	Route::get('/password/reset', 'Auth\AdminForgotPasswordController@showLinkRequestForm')->name('admin.password.request');
	Route::post('/password/email', 'Auth\AdminForgotPasswordController@sendResetLinkEmail')->name('admin.password.email');
	Route::get('/password/reset/{token}', 'Auth\AdminResetPasswordController@showResetForm')->name('admin.password.reset');
	Route::post('/password/reset', 'Auth\AdminResetPasswordController@reset');
});