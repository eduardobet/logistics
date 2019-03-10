<?php

Route::group(['middleware' => 'auth'], function () {
    Route::group(['prefix' => 'employee'], function () {
        Route::get('dashboard', 'Tenant\Employee\DashboardController@index')->name('tenant.employee.dashboard');

        Route::get('profile/me', 'Tenant\Employee\ProfileController@edit')->name('tenant.employee.profile.edit');
        Route::patch('profile/update', 'Tenant\Employee\ProfileController@update')->name('tenant.employee.profile.update');
    });
    
    // clients
    Route::get('client/list', 'Tenant\ClientController@index')->name('tenant.client.list')->middleware(['can:show-client']);
    Route::get('client/create', 'Tenant\ClientController@create')->name('tenant.client.create')->middleware(['can:create-client']);
    Route::post('client/store', 'Tenant\ClientController@store')->name('tenant.client.store');
    Route::get('client/{id}/edit', 'Tenant\ClientController@edit')->name('tenant.client.edit')->middleware(['can:edit-client']);
    Route::patch('client/{id}/update', 'Tenant\ClientController@update')->name('tenant.client.update');
    Route::get('client/extra-contacts-tmpl', 'Tenant\ClientController@econtactTmpl')->name('tenant.client.contact-tmpl');
    Route::delete('client/extra-contacts-destroy', 'Tenant\ClientController@deleteExtraContact')->name('tenant.client.extra-contact.destroy');
    Route::post('client/resend-welcome-email', 'Tenant\ClientController@resentWelcomeEmail')->name('tenant.client.welcome.email.resend');
    Route::get('client/{id}/show', 'Tenant\ClientController@show')->name('tenant.client.show')->middleware(['can:show-client']);

    //mailers
    Route::get('mailer/list', 'Tenant\MailerController@index')->name('tenant.mailer.list')->middleware(['can:show-mailer']);
    Route::get('mailer/mailer-tpl', 'Tenant\MailerController@getTmpl')->name('tenant.mailer.mailer-tpl');
    Route::get('mailer/create', 'Tenant\MailerController@create')->name('tenant.mailer.create')->middleware(['can:create-mailer']);
    Route::post('mailer/store', 'Tenant\MailerController@store')->name('tenant.mailer.store');
    Route::get('mailer/{id}/edit', 'Tenant\MailerController@edit')->name('tenant.mailer.edit')->middleware(['can:edit-mailer']);
    Route::patch('mailer/{id}/update', 'Tenant\MailerController@update')->name('tenant.mailer.update');
    Route::delete('mailer/mailer-destroy', 'Tenant\MailerController@destroy')->name('tenant.mailer.destroy');

    //warehouse
    Route::get('warehouse/list', 'Tenant\WarehouseController@index')->name('tenant.warehouse.list')->middleware(['can:show-warehouse']);
    Route::get('warehouses/export', 'Tenant\WarehouseController@export')->name('tenant.warehouse.export')->middleware(['can:show-warehouse']);
    Route::get('warehouse/invoice-tpl', 'Tenant\WarehouseController@invoiceTpl')->name('tenant.warehouse.invoice-tpl');
    Route::get('warehouse/{id}/print-sticker', 'Tenant\WarehouseController@sticker')->name('tenant.warehouse.print-sticker');
    Route::get('warehouse/invoice-detail-tmpl', 'Tenant\WarehouseController@invoiceDetTpl')->name('tenant.warehouse.invoice-detail-tmpl');
    Route::get('warehouse/create', 'Tenant\WarehouseController@create')->name('tenant.warehouse.create')->middleware(['can:create-warehouse']);
    Route::post('warehouse/store', 'Tenant\WarehouseController@store')->name('tenant.warehouse.store');
    Route::get('warehouse/{id}/edit', 'Tenant\WarehouseController@edit')->name('tenant.warehouse.edit')->middleware(['can:edit-warehouse']);
    Route::patch('warehouse/{id}/update', 'Tenant\WarehouseController@update')->name('tenant.warehouse.update');
    Route::post('warehouse/toggle', 'Tenant\WarehouseController@toggle')->name('tenant.warehouse.toggle');

    //cargo entry
    Route::get('cargo-entry/list', 'Tenant\CargoEntryController@index')->name('tenant.warehouse.cargo-entry.list');
    Route::get('cargo-entry/create', 'Tenant\CargoEntryController@create')->name('tenant.warehouse.cargo-entry.create');
    Route::post('cargo-entry/store', 'Tenant\CargoEntryController@store')->name('tenant.warehouse.cargo-entry.store');
    Route::get('cargo-entry/{id}/show', 'Tenant\CargoEntryController@show')->name('tenant.warehouse.cargo-entry.show');

    // invoice
    Route::get('invoice/list', 'Tenant\InvoiceController@index')->name('tenant.invoice.list')->middleware(['can:show-invoice']);
    Route::get('invoices/export', 'Tenant\InvoiceController@export')->name('tenant.invoice.export')->middleware(['can:show-invoice']);
    Route::get('invoice/create', 'Tenant\InvoiceController@create')->name('tenant.invoice.create')->middleware(['can:create-invoice']);
    Route::post('invoice/store', 'Tenant\InvoiceController@store')->name('tenant.invoice.store');
    Route::get('invoice/{id}/edit', 'Tenant\InvoiceController@edit')->name('tenant.invoice.edit')->middleware(['can:edit-invoice']);
    Route::patch('invoice/{id}/update', 'Tenant\InvoiceController@update')->name('tenant.invoice.update');
    Route::get('invoice/{id}/show', 'Tenant\InvoiceController@show')->name('tenant.invoice.show');
    Route::get('invoice/invoice-detail-tmpl', 'Tenant\InvoiceController@invoiceDetTpl')->name('tenant.invoice.invoice-detail-tmpl');
    Route::get('invoice/{id}/print-invoice', 'Tenant\InvoiceController@print')->name('tenant.invoice.print-invoice');
    Route::post('invoice/{id}/resend-invoice-email', 'Tenant\InvoiceController@resendInvoice')->name('tenant.invoice.invoice.resend');
    Route::post('invoice/penalize', 'Tenant\InvoiceController@penalize')->name('tenant.invoice.penalize');
    Route::post('invoice/inactive', 'Tenant\InvoiceController@inactive')->name('tenant.invoice.inactive');

    // payment
    Route::get('payment/list', 'Tenant\PaymentController@index')->name('tenant.payment.list')->middleware(['can:show-payment']);
    Route::get('payments/export', 'Tenant\PaymentController@export')->name('tenant.payment.export')->middleware(['can:show-payment']);
    Route::get('payment/{invoice_id}/create', 'Tenant\PaymentController@create')->name('tenant.payment.create')->middleware(['can:create-payment']);
    Route::post('payment/store', 'Tenant\PaymentController@store')->name('tenant.payment.store')->middleware(['can:create-payment']);
    Route::patch('payment/update', 'Tenant\PaymentController@update')->name('tenant.payment.update')->middleware(['can:create-payment']);
    Route::get('payment/{id}/show', 'Tenant\PaymentController@show')->name('tenant.payment.show')->middleware(['can:show-payment']);

    //incomes
    Route::get('income/list', 'Tenant\IncomeController@index')->name('tenant.income.list')->middleware(['can:show-payment']);
    
    // searching
    Route::get('search', 'Tenant\SearchController@search')->name('tenant.get.search');
});
