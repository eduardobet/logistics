<?php

use Illuminate\Database\Seeder;
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
        factory(Country::class)->create();
    }
}
