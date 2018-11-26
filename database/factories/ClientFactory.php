<?php

use Faker\Generator as Faker;
use Logistics\DB\Tenant\Client;

$factory->define(Client::class, function (Faker $faker) {
    return [
        "tenant_id" => 1,
        "branch_id" => 1,
        "created_by_code" => null,
        'first_name' => ($fName = $faker->unique()->firstname),
        'last_name' => ($lName = $faker->unique()->lastname),
        'full_name' => "$fName $lName",
        'pid' => 'E-8-124926',
        'email' => $faker->unique()->safeEmail(),
        'telephones' => '555-5555, 565-5425',
        'type' => 'C',
        'status' => 'A',
    ];
});
