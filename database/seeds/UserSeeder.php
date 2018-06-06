<?php

use Logistics\DB\User;
use Illuminate\Database\Seeder;
use Logistics\DB\Tenant\Tenant;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tenant = Tenant::whereDomain('https://middleton-services.test')->first();

        factory(User::class)->states('admin')->create([
            'tenant_id' => $tenant->id,
            'email' => 'main-admin@middleton-services.test',
        ]);

        factory(User::class)->states('employee')->create([
            'tenant_id' => $tenant->id,
            'email' => 'employee1@middleton-services.test',
        ]);
    }
}
