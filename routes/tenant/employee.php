<?php

Route::group(['middleware' => 'auth'], function () {
    Route::group(['prefix' => 'employee'], function () {
        Route::get('dashboard', 'Tenant\Employee\DashboardController@index')->name('tenant.employee.dashboard');

        Route::get('profile/me', 'Tenant\Employee\ProfileController@edit')->name('tenant.employee.profile.edit');
        Route::patch('profile/update', 'Tenant\Employee\ProfileController@update')->name('tenant.employee.profile.update');
    });
    
    // clients
    Route::get('client/list', 'Tenant\ClientController@index')->name('tenant.client.list');
    Route::get('client/create', 'Tenant\ClientController@create')->name('tenant.client.create');
    Route::post('client/store', 'Tenant\ClientController@store')->name('tenant.client.store');
    Route::get('client/{id}/edit', 'Tenant\ClientController@edit')->name('tenant.client.edit');
    Route::patch('client/{id}/update', 'Tenant\ClientController@update')->name('tenant.client.update');
    Route::get('client/extra-contacts-tmpl', 'Tenant\ClientController@econtactTmpl')->name('tenant.client.contact-tmpl');
});
