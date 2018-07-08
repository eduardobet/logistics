<?php

use Faker\Generator as Faker;
use Logistics\DB\Tenant\Tenant;

$factory->define(Tenant::class, function (Faker $faker) {
    return [
        'domain' => 'middleton-services.test',
        'name' => 'Middleton Services S.A.',
        'status' => 'A',
        'lang' => 'en',
        'address' => 'Centro Comercial Los Andes, Local G9-4, Arriba de las oficinas de Claro Pasillo de Cable Onda',
        'telephones' => '399-5706, 394-2899, 6519-4037',
        'emails' => "prla@tenant.com",
        'ruc' => "RUC",
        'dv' => "DV",
        'country_id' => 1,
    ];
});
