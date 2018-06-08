<?php

Route::group(['prefix' => 'employee', 'middleware' => 'auth'], function () {
    Route::get('dashboard', 'Tenant\Employee\DashboardController@index')->name('tenant.employee.dashboard');

    Route::get('profile/me', 'Tenant\Employee\ProfileController@edit')->name('tenant.employee.profile.edit');
    Route::patch('profile/update', 'Tenant\Employee\ProfileController@update')->name('tenant.employee.profile.update');

    // clients
    Route::get('client/list', 'Tenant\ClientController@index')->name('tenant.client.list');
    Route::get('client/create', 'Tenant\ClientController@create')->name('tenant.client.create');
    Route::post('client/store', 'Tenant\ClientController@store')->name('tenant.client.store');
});
