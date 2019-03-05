<?php

use Illuminate\Database\Seeder;
use Logistics\DB\Tenant\Tenant;

class InvoiceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tenant = Tenant::whereId(1)->first();
        $branch = $tenant->branches->where('name', '=', 'Los Andes 2')->first();
        $clientA = $tenant->clients->find(1);
        $clientB = $tenant->clients->find(2);

        $invoiceA = $tenant->invoices()->create([
            'branch_id' => $branch->id,
            'client_id' => $clientA->id,
            'total' => 160,
        ]);
        $invoiceA->details()->create([
            'qty' => 1,
            'type' => 1,
            'description' => 'Buying from amazon',
            'id_remote_store' => '122452222',
            'total' => 100,
        ]);
        $invoiceA->details()->create([
            'qty' => 1,
            'type' => 2,
            'description' => 'Buying from ebay',
            'id_remote_store' => '10448796566',
            'total' => 60,
        ]);
        $invoiceA->details()->create([
            'qty' => 1,
            'type' => 1,
            'description' => 'Comision tarjeta',
            'id_remote_store' => '10448796566',
            'total' => 5,
        ]);
        //payments
        $invoiceA->payments()->create([
            'tenant_id' => $invoiceA->tenant_id,
            'amount_paid' => 80,
            'payment_method' => 1,
            'payment_ref' => 'The client paid $80.00',
            'is_first' => true,
        ]);
        $invoiceA->payments()->create([
            'tenant_id' => $invoiceA->tenant_id,
            'amount_paid' => 40,
            'payment_method' => 2,
            'payment_ref' => 'The client paid $40.00',
            'is_first' => false,
        ]);


        $invoiceB = $tenant->invoices()->create([
            'branch_id' => $branch->id,
            'client_id' => $clientB->id,
            'total' => 200,
        ]);
        $invoiceB->details()->create([
            'qty' => 1,
            'type' => 1,
            'description' => 'Buying from bestbuy',
            'id_remote_store' => '582248224',
            'total' => 150,
        ]);
        $invoiceB->details()->create([
            'qty' => 1,
            'type' => 2,
            'description' => 'Buying from walmart',
            'id_remote_store' => '6822d5a4s',
            'total' => 50,
        ]);
        //payments
        $invoiceB->payments()->create([
            'tenant_id' => $invoiceB->tenant_id,
            'amount_paid' => 100,
            'payment_method' => 1,
            'payment_ref' => 'The client paid $100.00',
            'is_first' => true,
        ]);
        $invoiceB->payments()->create([
            'tenant_id' => $invoiceB->tenant_id,
            'amount_paid' => 20,
            'payment_method' => 1,
            'payment_ref' => 'The client paid $20.00',
            'is_first' => false,
        ]);
    }
}
