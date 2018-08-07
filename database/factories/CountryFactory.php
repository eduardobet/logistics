<?php

use Faker\Generator as Faker;
use Logistics\DB\Tenant\Country;

$factory->define(Country::class, function (Faker $faker) {
    return [
        'id' => 1,
        'name' => 'Panamá',
        'code' => 'PA',
        'iata' => 'PTY',
    ];
});
