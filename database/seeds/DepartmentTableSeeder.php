<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Logistics\DB\Tenant\Country;

class DepartmentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $panama = Country::where('name', 'Panamá')->first();

        $departments = [
            ['name' => 'Bocas del Toro', 'country_id' => $panama->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'capital' => 'Bocas del Toro', 'id' => 11],
            ['name' => 'Coclé', 'country_id' => $panama->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'capital' => 'Penonomé', 'id' => 12],
            ['name' => 'Colón', 'country_id' => $panama->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'capital' => 'Colón', 'id' => 13],
            ['name' => 'Chiriquí', 'country_id' => $panama->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'capital' => 'San José de David', 'id' => 14],
            ['name' => 'Darién', 'country_id' => $panama->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'capital' => 'La Palma', 'id' => 15],
            ['name' => 'Herrera', 'country_id' => $panama->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'capital' => 'Chitré', 'id' => 16],
            ['name' => 'Los Santos', 'country_id' => $panama->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'capital' => 'Las Tablas', 'id' => 17],
            ['name' => 'Panamá', 'country_id' => $panama->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'capital' => 'Ciudad de Panamá', 'id' => 18],
            ['name' => 'Veraguas', 'country_id' => $panama->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'capital' => 'Santiago', 'id' => 19],
            ['name' => 'Panamá Oeste', 'country_id' => $panama->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'capital' => 'La Chorrera', 'id' => 20],
        ];
        
        \DB::table('departments')->insert($departments);
    }
}
