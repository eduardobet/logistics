<?php

Route::group(['domain' => '{domain}', 'middleware' => 'tenant'], function () {
    $type = auth()->check() && auth()->user()->isAdmin() ? 'admin' : 'employee';

    Route::get('/home', function () {
        return "";
    })
    ->name('tenant.home');

    Route::get('/', 'Tenant\Employee\DashboardController@index')
    ->middleware('auth')
    ->name("tenant.{$type}.dashboard.home");

    //Tracking
    Route::get('tracking', 'Tenant\TrackingController@showTrackingForm')->name('tenant.tracking.get');

    //Misidentified package
    Route::get('malidentificados/list', 'Tenant\MisidentifiedController@index')->name('tenant.misidentified-package.index');
    Route::get('malidentificados/{id}/show', 'Tenant\MisidentifiedController@show')->name('tenant.misidentified-package.show');
    Route::get('malidentificados', 'Tenant\MisidentifiedController@create')->name('tenant.misidentified-package.create');
    Route::post('malidentificados', 'Tenant\MisidentifiedController@store')->name('tenant.misidentified-package.store');
    
    // auth
    Route::group(['prefix' => 'auth'], function () {
       
        // Authentication Routes...
        Route::get('login', 'Tenant\Auth\LoginController@showLoginForm')->name('tenant.auth.get.login');
        Route::post('login', 'Tenant\Auth\LoginController@login')->name('tenant.auth.post.login');
        Route::post('logout', 'Tenant\Auth\LoginController@logout')->name('tenant.auth.post.logout');

        // TODO: password reset
        Route::get('password/reset', 'Tenant\Auth\ForgotPasswordController@showLinkRequestForm')->name('tenant.user.password.request');
        Route::post('password/email', 'Tenant\Auth\ForgotPasswordController@sendResetLinkEmail')->name('tenant.user.password.email');
        Route::get('password/reset/{token}', 'Tenant\Auth\ResetPasswordController@showResetForm')->name('tenant.user.password.reset');
        Route::post('password/reset', 'Tenant\Auth\ResetPasswordController@reset')->name('tenant.user.password.post.reset');

        Route::get('unlock/{email}/{token}', 'Tenant\Auth\AccountActivationController@showUnlockForm')
            ->name('tenant.employee.get.unlock');
        Route::post('unlock', 'Tenant\Auth\AccountActivationController@unlock')
            ->name('tenant.employee.post.unlock');
    });

    require __DIR__ . '/admin.php';
    require __DIR__ . '/employee.php';

    if (app()->environment('local')) {
        require __DIR__ . '/../test-mails.php';
    }
});
