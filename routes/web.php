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
        return "Hello Tenant";
    });
});
