<?php

use Faker\Generator as Faker;
use Logistics\DB\Tenant\Tenant;

$factory->define(Tenant::class, function (Faker $faker) {
    return [
        'domain' => 'middleton-services.test',
        'name' => 'Middleton Services S.A.',
        'status' => 'A',
        'lang' => 'en',
    ];
});
