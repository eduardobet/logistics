<?php

Route::group(['domain' => '{domain}', 'middleware' => 'tenant'], function () {
    Route::get('/', function () {
        return view()->shared('tenant');
    })->name('tenant.home');

    // auth
    Route::group(['prefix' => 'auth'], function () {
        // Authentication Routes...
        Route::get('login', 'Tenant\Auth\LoginController@showLoginForm')->name('tenant.auth.get.login');
        Route::post('login', 'Tenant\Auth\LoginController@login')->name('tenant.auth.post.login');
        Route::post('logout', 'Tenant\Auth\LoginController@logout')->name('tenant.auth.post.logout');

        // TODO: password reset
        Route::get('password/reset', 'Tenant\Auth\ForgotPasswordController@showLinkRequestForm')->name('tenant.user.password.request');
        Route::post('password/email', 'Tenant\Auth\ForgotPasswordController@sendResetLinkEmail')->name('tenant.user.password.email');
        Route::get('password/reset/{token}', 'Tenant\Auth\ResetPasswordController@showResetForm')->name('tenant.user.password.reset')->middleware('signed');
        Route::post('password/reset', 'Tenant\Auth\ResetPasswordController@reset')->name('tenant.user.password.post.reset');

        Route::get('unlock/{email}/{token}', 'Tenant\Auth\AccountActivationController@showUnlockForm')
            ->name('tenant.employee.get.unlock')
            ->middleware('signed');
        Route::post('unlock', 'Tenant\Auth\AccountActivationController@unlock')
            ->name('tenant.employee.post.unlock');
    });

    require __DIR__ . '/admin.php';
    require __DIR__ . '/employee.php';

    if (app()->environment('local')) {
        require __DIR__ . '/../test-mails.php';
    }
});
