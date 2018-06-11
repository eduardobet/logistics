<?php

use Illuminate\Database\Seeder;
use Logistics\DB\Tenant\Client;
use Logistics\DB\Tenant\Tenant;

class ClientTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tenant = Tenant::whereDomain('https://middleton-services.test')->first();

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, ]);
        $branch = $tenant->branches->first();

        $client->genBox($branch->id, $branch->code);
    }
}
