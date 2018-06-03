<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Logistics\DB\User::class, function (Faker $faker) {
    return [
        'first_name' => $faker->firstname,
        'last_name' => $faker->lastname,
        'email' => $faker->unique()->safeEmail,
        'password' => '$2y$10$Ca6FGHO9VPFpCuOyKFEVnO8nwU4UJGdYDEbXE2resVxYZ25jcnrRO', //secret123
        'remember_token' => str_random(10),
        'tenant_id' => null,
        'status' => 'A',
    ];
});

$factory->state(Logistics\DB\User::class, 'admin', function (Faker $faker) {
    return [
        'type' => 'A',
        'is_main_admin' => true,
    ];
});

$factory->state(Logistics\DB\User::class, 'employee', function (Faker $faker) {
    return [
        'type' => 'E',
    ];
});
