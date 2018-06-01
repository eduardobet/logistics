<?php

if (!app()->runningInConsole()) {
    get_exe_queries();
}

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::group(['domain' => 'logistics.test'], function () {
    Route::get('/', function () {
        return view('welcome');
    })->name('app.home');
});

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
    });

    Route::group(['middleware' => ['auth', 'admin'], 'prefix' => 'admin'], function () {
        Route::get('dashboard', 'Tenant\Admin\DashboardController@index')->name('tenant.admin.dashboard');

        // employee creation
        Route::get('employee/create', 'Tenant\Admin\EmployeeController@create')->name('tenant.admin.employee.create');
        Route::post('employee/store', 'Tenant\Admin\EmployeeController@store')->name('tenant.admin.employee.store');
    });

    Route::group(['prefix' => 'employee'], function () {
        Route::get('dashboard', 'Tenant\Employee\DashboardController@index')->name('tenant.employee.dashboard');
    });
});

//Auth::routes();

//Route::get('/home', 'HomeController@index')->name('home');
