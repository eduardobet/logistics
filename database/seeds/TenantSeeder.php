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
            ['type' => 'A', 'address' => '8450 NW 70 TH ST MIAMI, FLORIDA 33166-2687', 'telephones' => '+1(786)3252841', 'status' => 'A', ],
            ['type' => 'M', 'address' => '8454 NW 70 TH ST MIAMI, FLORIDA 33166', 'telephones' => '+1(786)3252841', 'status' => 'A', ],
        ]);

        $tenant->branches()->createMany([
            [
                'name' => 'Miami',
                'address' => '123 street ave',
                'telephones' => '56166758524',
                'emails' => "mia@tenant.com",
                'code' => 'MIA',
                'real_price' => 2.50,
                'vol_price' => 1.75,
                'dhl_price' => 2.25,
                'maritime_price' => 250,
                'should_invoice' => true,
            ],
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
                'should_invoice' => true,
            ]
        ]);
    }
}
