<?php

use Faker\Generator as Faker;
use Logistics\DB\Tenant\Tenant;

$factory->define(Tenant::class, function (Faker $faker) {
    $domain = app()->environment('production') ? 'sealcargotrack.com' : 'sealcargotrack.test';
    
    return [
        'domain' => $domain,
        'name' => 'Seal Logistics',
        'status' => 'A',
        'lang' => 'es',
        'address' => 'Centro Comercial Los Andes, Local G9-4, Arriba de las oficinas de Claro Pasillo de Cable Onda',
        'telephones' => '399-5706, 394-2899, 6519-4037',
        'emails' => "prla@sealcargotrack.com",
        'ruc' => "RUC",
        'dv' => "DV",
        'country_id' => 1,
        'logo' => 'tenant/1/images/logos/logo.png',
        'timezone' => 'America/Panama',
      

        'mail_driver' => env('MAIL_DRIVER', 'smtp'),
        'mail_host' => env('MAIL_HOST', 'smtp.mailtrap.io'),
        'mail_port' => env('MAIL_PORT', '2525'),
        'mail_username' => env('MAIL_USERNAME', '488480be968d7466e'),
        'mail_password' => env('MAIL_PASSWORD', '67dd8cd11b8dc9'),
        'mail_encryption' => env('MAIL_ENCRYPTION', 'null'),
        'mail_from_address' => env('MAIL_FROM_ADDRESS', 'contact@tenant.com'),
        'mail_from_name' => env('MAIL_FROM_NAME', 'The tenant'),
        'mailgun_domain' => env('MAILGUN_DOMAIN', ''),
        'mailgun_secret' => env('MAILGUN_SECRET', ''),
    ];
});
