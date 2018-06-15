<?php

use Illuminate\Database\Seeder;
use Logistics\DB\Tenant\Tenant;
use Logistics\DB\Tenant\Position;

class PositionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tenant = Tenant::whereId(1)->first();

        $tenant->positions()->createMany([
            [ "name" => "Super Administrador", "status" => "A", ],
            [ "name" => "Administrador / Gerente", "status" => "A", ],
            [ "name" => "Contador / Contabilidad","status" => "A", ],
            [ "name" => "Vendedor / Operador Caja","status" => "A", ],
            [ "name" => "Vendedor / Atencion al Cliente","status" => "A", ],
            [ "name" => "Bodega / Entradas Paquetes","status" => "A", ],
            [ "name" => "Chofer / Repartidor","status" => "A", ],
        ]);
    }
}
