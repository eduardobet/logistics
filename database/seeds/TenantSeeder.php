<?php

use Illuminate\Database\Seeder;
use Logistics\DB\Tenant\Tenant;
use Logistics\DB\Tenant\Branch;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tenant = factory(Tenant::class)->create();

        $tenant->remoteAddresses()->createMany([
            ['type' => 'A', 'address' => 'In the middle of remote air', 'telephones' => '555-5555', 'status' => 'A', ],
            ['type' => 'M', 'address' => 'In the middle of remote maritimes', 'telephones' => '555-5555', 'status' => 'A', ],
        ]);

        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
    }
}
