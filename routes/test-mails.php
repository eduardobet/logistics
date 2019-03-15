<?php

use Logistics\DB\Tenant\Tenant;
use Logistics\Mail\Tenant\InvoiceCreated;
use Logistics\Mail\Tenant\PaymentCreated;
use Logistics\Mail\Tenant\WelcomeClientEmail;
use Logistics\Mail\Tenant\WelcomeEmployeeEmail;
use Logistics\Mail\Tenant\WarehouseReceiptEmail;

Route::get('test-welcome-employee', function () {
    $tenant = Tenant::first();
    $branch = $tenant->branches->first();
    $employee = $tenant->employees->first();

    return new WelcomeEmployeeEmail($tenant, $employee);
});

Route::get('test-welcome-client', function () {
    $tenant = Tenant::first();
    $branch = $tenant->branches->first();
    $client = $tenant->clients->first();

    return new WelcomeClientEmail($tenant, $client);
});

Route::get('test-send-invoice', function () {
    $tenant = Tenant::first();
    $branch = $tenant->branches->last();
    $client = $tenant->clients->first();

    $invoice = $tenant->invoices()->create([
        'branch_id' => $branch->id,
        'client_id' => $client->id,
        'total' => 160,
    ]);

    $detailA = $invoice->details()->create([
        'qty' => 1,
        'type' => 1,
        'description' => 'Buying from amazon',
        'id_remote_store' => '122452222',
        'total' => 100,
    ]);

    $detailB = $invoice->details()->create([
        'qty' => 1,
        'type' => 2,
        'description' => 'Buying from ebay',
        'id_remote_store' => '10448796566',
        'total' => 60,
    ]);

    $payment = $invoice->payments()->create([
        'tenant_id' => $invoice->tenant_id,
        'amount_paid' => 80,
        'payment_method' => 1,
        'payment_ref' => 'The client paid $80.00',
        'is_first' => true,
    ]);

    return new InvoiceCreated($tenant, $invoice);
});

Route::get('test-send-payment', function () {
    $tenant = Tenant::first();
    $branch = $tenant->branches->last();
    $client = $tenant->clients->first();

    $invoice = $tenant->invoices()->create([
        'branch_id' => $branch->id,
        'client_id' => $client->id,
        'total' => 160,
    ]);

    $invoice->details()->create([
        'qty' => 1,
        'type' => 1,
        'description' => 'Buying from amazon',
        'id_remote_store' => '122452222',
        'total' => 100,
    ]);

    $payment = $invoice->payments()->create([
        'tenant_id' => $invoice->tenant_id,
        'amount_paid' => 100,
        'payment_method' => 1,
        'payment_ref' => 'The client paid $100.00',
        'is_first' => true,
    ]);

    return new PaymentCreated($tenant, $client, $invoice, $payment);
});


Route::get('test-wh-receipt/{id}', function () {
    $tenant = Tenant::first();
    $warehouse = $tenant->warehouses();

    $warehouse = $warehouse->where('id', request('id'))->firstOrFail();

    $data = [
        'warehouse' => $warehouse,
        'branchTo' => $tenant->branches()->select(['tenant_id', 'id', 'name', 'address', 'telephones'])->find($warehouse->branch_to),
        'mailer' => $tenant->mailers()->select(['tenant_id', 'id', 'name'])->find($warehouse->mailer_id),
        'client' => $tenant->clients()
            ->with('branch')
            ->select(['tenant_id', 'id', 'first_name', 'last_name', 'address', 'email', 'telephones', 'branch_id', 'manual_id'])
            ->find($warehouse->client_id),
        'invoice' => $warehouse->invoice()
            ->with('details')->first(),
    ];

    $pdf = \PDF::loadView('tenant.warehouse.receipt', $data);

    $data['pdf'] = $pdf;

    $email = new WarehouseReceiptEmail($tenant, $data);

    return $email;
});
