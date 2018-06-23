<?php

Route::group(['tenant-domain' => '{tenant-domain}', 'middleware' => 'tenant'], function () {
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
        /*Route::get('password/reset', 'Auth\Tenant\ForgotPasswordController@showLinkRequestForm')->name('Tenant.user.password.request');
        Route::post('password/email', 'Auth\Tenant\ForgotPasswordController@sendResetLinkEmail')->name('Tenant.user.password.email');
        Route::get('password/reset/{token}', 'Auth\Tenant\ResetPasswordController@showResetForm')->name('Tenant.user.password.reset');
        Route::post('password/reset', 'Auth\Tenant\ResetPasswordController@reset')->name('client.user.password.post.reset');*/

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
