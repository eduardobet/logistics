<?php

use Logistics\DB\Tenant\Tenant;
use Logistics\Mail\Tenant\WelcomeClientEmail;
use Logistics\Mail\Tenant\WelcomeEmployeeEmail;

Route::get('test-welcome-employee', function () {
    $tenant = Tenant::first();
    $branch = $tenant->branches->first();
    $employee = $tenant->employees->first();

    return new WelcomeEmployeeEmail($tenant, $employee);
});

Route::get('test-welcome-client', function () {
    $tenant = Tenant::first();
    $branch = $tenant->branches->first();
    $client = $tenant->clients->first();

    return new WelcomeClientEmail($tenant, $client);
});
