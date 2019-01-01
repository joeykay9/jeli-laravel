<?php

Route::get('/', function () {
    return 'Jeli Business!';
});


Route::get('/home', 'BusinessHomeController@index')->name('business.home');