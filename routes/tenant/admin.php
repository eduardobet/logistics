<?php

Route::group(['middleware' => ['auth', 'admin'], 'prefix' => 'admin'], function () {
    Route::get('dashboard', 'Tenant\Employee\DashboardController@index')->name('tenant.admin.dashboard');

    // company
    Route::get('dashboard/company/edit', 'Tenant\Admin\CompanyController@edit')->name('tenant.admin.company.edit');
    Route::patch('dashboard/company/update', 'Tenant\Admin\CompanyController@update')->name('tenant.admin.company.update');
    Route::get('dashboard/company/remote-template', 'Tenant\Admin\CompanyController@getRemoteTpl')->name('tenant.compnay.remote-addr-tmpl');
    Route::get('dashboard/company/condition-template', 'Tenant\Admin\CompanyController@getConditionTpl')->name('tenant.compnay.condition-tmpl');

    // Positions
    Route::get('dashboard/positions/list', 'Tenant\Admin\PositionController@index')->name('tenant.admin.position.list');
    Route::get('dashboard/positions/create', 'Tenant\Admin\PositionController@create')->name('tenant.admin.position.create');
    Route::post('dashboard/positions/store', 'Tenant\Admin\PositionController@store')->name('tenant.admin.position.store');
    Route::get('dashboard/position/{id}/edit', 'Tenant\Admin\PositionController@edit')->name('tenant.admin.position.edit');
    Route::patch('dashboard/position/{id}/update', 'Tenant\Admin\PositionController@update')->name('tenant.admin.position.update');

    //Branches
    Route::get('dashboard/branches/list', 'Tenant\Admin\BranchController@index')->name('tenant.admin.branch.list');
    Route::get('dashboard/branches/create', 'Tenant\Admin\BranchController@create')->name('tenant.admin.branch.create');
    Route::post('dashboard/branches/store', 'Tenant\Admin\BranchController@store')->name('tenant.admin.branch.store');
    Route::get('dashboard/branch/{id}/edit', 'Tenant\Admin\BranchController@edit')->name('tenant.admin.branch.edit');
    Route::patch('dashboard/branch/update', 'Tenant\Admin\BranchController@update')->name('tenant.admin.branch.update');
    

    // employees
    Route::get('dashboard/employees', 'Tenant\Admin\EmployeeController@index')->name('tenant.admin.employee.list');
    Route::get('dashboard/employees/create', 'Tenant\Admin\EmployeeController@create')->name('tenant.admin.employee.create');
    Route::post('dashboard/employees/store', 'Tenant\Admin\EmployeeController@store')->name('tenant.admin.employee.store');
    Route::get('dashboard/employees/{id}/edit', 'Tenant\Admin\EmployeeController@edit')->name('tenant.admin.employee.edit');
    Route::patch('dashboard/employees/update', 'Tenant\Admin\EmployeeController@update')->name('tenant.admin.employee.update');
});
