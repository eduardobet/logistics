<?php

use Faker\Generator as Faker;
use Logistics\DB\Tenant\Permission;

$factory->define(Permission::class, function (Faker $faker) {
    return [
        'name' => 'Registrar Warehouse',
        'slug' => 'create-wh',
        'tenant_id' => 1,
        'header' => 'Warehouse',
    ];
});
