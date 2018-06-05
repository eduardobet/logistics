<?php

Route::group(['middleware' => ['auth', 'admin'], 'prefix' => 'admin'], function () {
    Route::get('dashboard', 'Tenant\Admin\DashboardController@index')->name('tenant.admin.dashboard');

    // company
    Route::get('dashboard/company/edit', 'Tenant\Admin\CompanyController@edit')->name('tenant.admin.company.edit');
    Route::patch('dashboard/company/update', 'Tenant\Admin\CompanyController@update')->name('tenant.admin.company.update');

    // employee creation
    Route::get('dashboard/employees', 'Tenant\Admin\EmployeeController@index')->name('tenant.admin.employee.list');
    Route::get('dashboard/employees/create', 'Tenant\Admin\EmployeeController@create')->name('tenant.admin.employee.create');
    Route::post('dashboard/employees/store', 'Tenant\Admin\EmployeeController@store')->name('tenant.admin.employee.store');
    Route::get('dashboard/employees/{id}/edit', 'Tenant\Admin\EmployeeController@edit')->name('tenant.admin.employee.edit');
    Route::patch('dashboard/employees/update', 'Tenant\Admin\EmployeeController@update')->name('tenant.admin.employee.update');
});
