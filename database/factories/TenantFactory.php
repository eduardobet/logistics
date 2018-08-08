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
        'logo' => 'tenant/1/images/logos/logo.png',

        'mail_driver' => 'smtp',
        'mail_host' => 'smtp.mailtrap.io',
        'mail_port' => '2525',
        'mail_username' => '488480be968d7466e',
        'mail_password' => '67dd8cd11b8dc9',
        'mail_encryption' => 'null',
        'mail_from_address' => 'contact@tenant.com',
        'mail_from_name' => 'The tenant',
    ];
});
