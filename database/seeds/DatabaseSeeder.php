<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CountryTableSeeder::class);
        $this->call(DepartmentTableSeeder::class);
        $this->call(ZoneTableSeeder::class);

        if (!app()->environment('production')) {
            $this->call(TenantSeeder::class);
            $this->call(PermissionTableSeeder::class);
            $this->call(PositionTableSeeder::class);
            $this->call(UserSeeder::class);
            $this->call(ClientTableSeeder::class);
            $this->call(InvoiceTableSeeder::class);
        }
    }
}
