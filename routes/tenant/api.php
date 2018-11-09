<?php

Route::group(['prefix' => 'api'], function () {
    Route::get('departments/{parentId}', 'Tenant\Api\DepartmentController@index')->name('tenant.api.department');
    Route::get('zones/{parentId}', 'Tenant\Api\ZoneController@index')->name('tenant.api.zone');
    Route::get('clients/{parentId}', 'Tenant\Api\ClientController@index')->name('tenant.api.clients');

    Route::post('tracking', 'Tenant\TrackingController@track')->name('tenant.tracking.post');

    Route::post('malidentificados', 'Tenant\MisidentifiedController@store')->name('tenant.misidentified-package.store');
});
