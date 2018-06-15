<?php

use Illuminate\Database\Seeder;
use Logistics\DB\Tenant\Tenant;
use Logistics\DB\Tenant\Country;

class CountryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tenant = Tenant::whereId(1)->first();
        
        Country::create([
            'tenant_id' => $tenant->id,
            'name' => 'Panamá',
        ]);
    }
}
