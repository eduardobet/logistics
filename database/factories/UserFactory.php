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
        'email' => '2brainzdev@gmail.com',
        'password' => '$2y$10$Ca6FGHO9VPFpCuOyKFEVnO8nwU4UJGdYDEbXE2resVxYZ25jcnrRO', //secret123
        'remember_token' => str_random(10),
        'tenant_id' => null,
        'status' => 'A',
        'avatar' => null,
        'pid' => 'PID',
        'telephones' => '6232-5312,6345-5472',
        'avatar' => 'tenant/1/images/avatars/avatar.png',
    ];
});

$factory->state(Logistics\DB\User::class, 'admin', function (Faker $faker) {
    return [
        'type' => 'A',
        'is_main_admin' => true,
        'permissions' => [],
        'position' => 1,
    ];
});

$factory->state(Logistics\DB\User::class, 'employee', function (Faker $faker) {
    return [
        'type' => 'E',
        'permissions' => [],
        'email' => $faker->unique()->safeEmail
    ];
});
