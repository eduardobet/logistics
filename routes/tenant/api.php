<?php

Route::group(['prefix' => 'api'], function () {
    Route::get('departments/{parentId}', 'Tenant\Api\DepartmentController@index')->name('tenant.api.department');
    Route::get('zones/{parentId}', 'Tenant\Api\ZoneController@index')->name('tenant.api.zone');
});
