<?php

use Illuminate\Database\Seeder;
use Logistics\DB\Tenant\Tenant;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tenant = Tenant::whereId(1)->first();

        $tenant->permissions()->createMany([
            // mailer
            ["name" => "Registrar Remitentes", "slug" => "create-mailer", "header" => "Remitentes",],
            ["name" => "Editar Remitentes", "slug" => "edit-mailer", "header" => "Remitentes",],
            ["name" => "Ver Remitentes", "slug" => "show-mailer", "header" => "Remitentes",],

            // warehouse
            ["name" => "Registrar Warehouse", "slug" => "create-warehouse", "header" => "Warehouse",],
            ["name" => "Editar Warehouse", "slug" => "edit-warehouse", "header" => "Warehouse",],
            ["name" => "Ver Warehouse", "slug" => "show-warehouse", "header" => "Warehouse",],

            // reca
            ["name" => "Registrar carga", "slug" => "create-reca", "header" => "Registro de carga",],
            ["name" => "Ver carga", "slug" => "show-reca", "header" => "Registro de carga",],

            // misidentified-pa ckage
            ["name" => "Registrar Paquetes", "slug" => "create-misreca", "header" => "Paquetes malidentificados",],
            ["name" => "Ver Paquetes ", "slug" => "show-misreca", "header" => "Paquetes malidentificados",],

            // Package
            ["name" => "Registrar Paquetes", "slug" => "create-package", "header" => "Paquetes",],
            ["name" => "Editar Paquetes", "slug" => "edit-package", "header" => "Paquetes",],
            ["name" => "Ver Paquetes", "slug" => "show-package", "header" => "Paquetes",],

            // Client
            ["name" => "Registrar Clientes", "slug" => "create-client", "header" => "Clientes",],
            ["name" => "Editar Clientes", "slug" => "edit-client", "header" => "Clientes",],
            ["name" => "Ver Clientes", "slug" => "show-client", "header" => "Clientes",],

            // invoice
            ["name" => "Registrar Facturas", "slug" => "create-invoice", "header" => "Facturas",],
            ["name" => "Editar Facturas", "slug" => "edit-invoice", "header" => "Facturas",],
            ["name" => "Ver Facturas", "slug" => "show-invoice", "header" => "Facturas",],
            ["name" => "Eliminar Facturas", "slug" => "delete-invoice", "header" => "Facturas",],

            // payment
            ["name" => "Registrar Pagos", "slug" => "create-payment", "header" => "Pagos",],
            ["name" => "Editar Pagos", "slug" => "edit-payment", "header" => "Pagos",],
            ["name" => "Ver Pagos", "slug" => "show-payment", "header" => "Pagos",],

            // Petty cach
            ["name" => "Registrar Caja chica", "slug" => "create-petty-cash", "header" => "Caja chica",],
            ["name" => "Editar Caja chica", "slug" => "edit-petty-cash", "header" => "Caja chica",],
            ["name" => "Ver Caja chica", "slug" => "show-petty-cash", "header" => "Caja chica",],

            // Accountant
            ["name" => "Ver movimientos monetarios", "slug" => "show-money-movments", "header" => "Movimientos monetarios",],
            ["name" => "Ver historial de compras", "slug" => "show-purchases-history", "header" => "Movimientos monetarios",],
            ["name" => "Ver historial de cobros", "slug" => "show-charges-history", "header" => "Movimientos monetarios",],
            ["name" => "Ver historial de gastos", "slug" => "show-expenses-history", "header" => "Movimientos monetarios",],
            ["name" => "Ver estatus de cuentas pendientes", "slug" => "show-pending-accounts", "header" => "Movimientos monetarios",],

            // Searching
            ["name" => "Búsqueda", "slug" => "search", "header" => "Búsqueda",],
        ]);
    }
}
