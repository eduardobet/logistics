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

        $tenant->branches()->createMany([
            [
                'name' => 'Los Andes 2',
                'address' => 'Centro Comercial Los Andes, Local G9-4, Arriba de las oficinas de Claro Pasillo de Cable Onda',
                'telephones' => '399-5706, 394-2899, 6519-4037',
                'emails' => "prla@tenant.com",
                'code' => 'PRLA',
                'real_price' => 2.50,
                'vol_price' => 1.75,
                'dhl_price' => 2.25,
                'maritime_price' => 250,
            ]
        ]);
    }
}
