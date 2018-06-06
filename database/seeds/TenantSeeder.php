<?php

use Illuminate\Database\Seeder;
use Logistics\DB\Tenant\Tenant;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Tenant::class)->create();
    }
}
