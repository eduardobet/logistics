<?php

use Faker\Generator as Faker;
use Logistics\DB\Tenant\Box;

$factory->define(Box::class, function (Faker $faker) {
    return [
        'tenant_id' => 1,
        'client_id' => 1,
        'branch_id' => 1,
        'branch_code' => 'B-CODE',
        'status' => 'A',
    ];
});
