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
        $tenant = Tenant::whereId(1)->first();

        $clientA = factory(Client::class)->create(['tenant_id' => $tenant->id, ]);
        $clientB = factory(Client::class)->create(['tenant_id' => $tenant->id, ]);
        $branch = $tenant->branches->where('name', '=', 'Los Andes 2')->first();

        $clientA->genBox($branch->id, $branch->code);
        $clientB->genBox($branch->id, $branch->code);
    }
}
