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
    Route::delete('client/extra-contacts-destroy', 'Tenant\ClientController@deleteExtraContact')->name('tenant.client.extra-contact.destroy');
    Route::post('client/resend-welcome-email', 'Tenant\ClientController@resentWelcomeEmail')->name('tenant.client.welcome.email.resend');

    //mailers
    Route::get('mailer/list', 'Tenant\MailerController@index')->name('tenant.mailer.list');
    Route::get('mailer/create', 'Tenant\MailerController@create')->name('tenant.mailer.create');
    Route::post('mailer/store', 'Tenant\MailerController@store')->name('tenant.mailer.store');
    Route::get('mailer/{id}/edit', 'Tenant\MailerController@edit')->name('tenant.mailer.edit');
    Route::patch('mailer/{id}/update', 'Tenant\MailerController@update')->name('tenant.mailer.update');

    //warehouse
    Route::get('warehouse/list', 'Tenant\WarehouseController@index')->name('tenant.warehouse.list');
    Route::get('warehouse/create', 'Tenant\WarehouseController@create')->name('tenant.warehouse.create');
    Route::post('warehouse/store', 'Tenant\WarehouseController@store')->name('tenant.warehouse.store');
    Route::get('warehouse/{id}/edit', 'Tenant\WarehouseController@edit')->name('tenant.warehouse.edit');
    Route::patch('warehouse/{id}/update', 'Tenant\WarehouseController@update')->name('tenant.warehouse.update');
});
