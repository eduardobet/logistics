<?php

Route::group(['domain' => 'logistics.test'], function () {
    Route::get('/', function () {
        return view('welcome');
    })->name('app.home');
});


//Auth::routes();

//Route::get('/home', 'HomeController@index')->name('home');
