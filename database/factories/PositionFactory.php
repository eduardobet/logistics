<?php

use Faker\Generator as Faker;
use Logistics\DB\Tenant\Position;

$factory->define(Position::class, function (Faker $faker) {
    return [
        'name' => 'Position Name',
        'status' => 'A',
        'description' => 'The description',
        'tenant_id' => 1,
    ];
});
