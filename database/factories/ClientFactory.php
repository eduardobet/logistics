<?php

use Faker\Generator as Faker;
use Logistics\DB\Tenant\Client;

$factory->define(Client::class, function (Faker $faker) {
    return [
        "tenant_id" => 1,
        "created_by_code" => null,
        'first_name' => $faker->unique()->firstname,
        'last_name' => $faker->unique()->lastname,
        'pid' => 'E-8-124926',
        'email' => $faker->unique()->safeEmail(),
        'telephones' => '555-5555, 565-5425',
        'type' => 'C',
        'status' => 'A',
    ];
});
