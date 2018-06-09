<?php

use Faker\Generator as Faker;
use Logistics\DB\Tenant\Client;

$factory->define(Client::class, function (Faker $faker) {
    return [
        "tenant_id" => 1,
        "created_by_code" => null,
        'first_name' => 'The',
        'last_name' => 'Client',
        'pid' => 'E-8-124926',
        'email' => 'client@company.com',
        'telephones' => '555-5555, 565-5425',
        'type' => 'C',
        'status' => 'A',
    ];
});
