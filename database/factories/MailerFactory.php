<?php

use Faker\Generator as Faker;
use Logistics\DB\Tenant\Mailer;

$factory->define(Mailer::class, function (Faker $faker) {
    return [
        'name' => 'The Mailer',
        'status' => 'A',
        'description' => 'The description of the mailer',
        'tenant_id' => 1,
        'vol_price' => 1.75,
        'real_price' => 2.5,
    ];
});
