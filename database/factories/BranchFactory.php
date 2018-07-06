<?php

use Faker\Generator as Faker;

$factory->define(\Logistics\DB\Tenant\Branch::class, function (Faker $faker) {
    return [
        'name' => 'Branch name',
        'address' => 'In the middle of nowhere',
        'telephones' => '555-5555, 6754-6754',
        'faxes' => '655-5555, 6854-6854',
        'emails' => 'branch@client.com',
        'lat' => null,
        'lng' => null,
        'status' => 'A',
        'tenant_id' => 0,
        'code' => 'CODE',
        'ruc' => null,
        'dv' => null,
        'logo' => null,
        'real_price' => 2.50,
        'vol_price' => 1.75,
        'dhl_price' => 2.25,
        'maritime_price' => 250,
    ];
});
