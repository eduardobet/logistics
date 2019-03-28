<?php

namespace Tests\Feature\Tenant\Warehouse;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Box;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Client;
use Logistics\DB\Tenant\Mailer;
use Logistics\DB\Tenant\Invoice;
use Illuminate\Support\Facades\Queue;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Logistics\Jobs\Tenant\SendInvoiceCreatedEmail;
use Logistics\Jobs\Tenant\SendPaymentCreatedEmail;

class WarehouseEditionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_like_this_tenant_user()
    {
        // $this->withoutExceptionHandling();
        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.warehouse.edit', [$tenant->domain, 1]), []);
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
    }

    /** @test */
    public function warehouse_cannot_be_updated_with_invalid_inputs()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        $response = $this->actingAs($admin)->patch(route('tenant.warehouse.update', [$tenant->domain, 1]), [
            'reception_branch' => 'XXX'
        ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.warehouse.edit', [$tenant->domain, 1]));

        $response->assertSessionHasErrors([
            'branch_from', 'branch_to', 'type', 'tot_packages', 'tot_weight',
        ]);
    }

    /** @test */
    public function it_validates_the_id_when_manually_provided()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create(['migration_mode' => true, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        $response = $this->actingAs($admin)->patch(route('tenant.warehouse.update', [$tenant->domain, 1]), [
            'manual_id' => 'xxx',
        ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.warehouse.edit', [$tenant->domain, 1]));

        $response->assertSessionHasErrors([
            'manual_id',
        ]);
    }

    /** @test */
    public function it_updates_the_warehouse_for_direct_comission()
    {
        $this->withoutExceptionHandling();

        Queue::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $mailer = factory(Mailer::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id,]);

        $this->actingAs($admin);

        \Gate::define('edit-warehouse', function ($admin) {
            return true;
        });

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, ]);

        factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);

        $warehouse = $tenant->warehouses()->create([
            'branch_to' => $branchB->id,
            'branch_from' => $branch->id,
            'client_id' => $client->id,
            'mailer_id' => $mailer->id,
            'trackings' => '1234',
            'reference' => 'The reference',
            'qty' => 1,
            'type' => 'A',
        ]);

        $invoice = Invoice::create([
            'tenant_id' => $tenant->id,
            'warehouse_id' => $warehouse->id,
            'client_id' => $client->id,
            'branch_id' => $warehouse->branch_to,
            'created_by_code' => $admin->id,
            'updated_by_code' => $admin->id,
            'client_name' => 'The Name of the client',
            'client_email' => 'email@client.com',
            'volumetric_weight' => 8,
            'real_weight' => 9,
            'manual_id' => 9,
            'total' => $mailer->vol_price * 8,
        ]);

        $detail = $invoice->details()->create([
            'qty' => 1,
            'type' => 1,
            'length' => 10,
            'width' => 10,
            'height' => 10,
            'vol_weight' => 8,
            'real_weight' => 9,
        ]);

        $response = $this->actingAs($admin)->get(route('tenant.warehouse.edit', [$tenant->domain, $warehouse->id]));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.warehouse.edit');
        $response->assertViewHas(['userBranches', 'branches', 'mailers', 'warehouse', ]);

        $response = $this->actingAs($admin)->patch(route('tenant.warehouse.update', [$tenant->domain, $warehouse->id]), [
            'branch_from' => $branch->id,
            'branch_to' => $branchB->id,
            'client_id' => $client->id,
            'mailer_id' => $mailer->id,
            'trackings' => '1234\n3654',
            'reference' => 'The reference update',
            'qty' => 2,
            'type' => 'A',
            'tot_packages' => 2,
            'tot_weight' => 21,

            //
            'client_name' => 'The client of the direct comission',
            'client_email' => 'direct.comission@client.test',
            'total_volumetric_weight' => 21,
            'total_real_weight' => 23,
            'total' => $mailer->real_price * 23,
            'notes' => 'The notes of the invoice updated',
            'invoice_id' => $invoice->id,
            'manual_id' => 15, // invoice
            'invoice_detail' => [
                ['qty' => 1, 'type' => 1, 'length' => 10, 'width' => 10, 'height' => 10, 'vol_weight' => 8, 'real_weight' => 9 , 'tracking' => '4444444', 'wdid' => $detail->id, ],
                ['qty' => 1, 'type' => 2, 'length' => 12, 'width' => 12, 'height' => 12, 'vol_weight' => 13, 'real_weight' => 14,'tracking' => '55555555',  ],
            ]
        ]);
        $response->assertRedirect(route('tenant.warehouse.edit', [$tenant->domain, 1]));

        $this->assertDatabaseHas('warehouses', [
            "tenant_id" => $tenant->id,
            "updated_by_code" => $admin->id,
            'branch_from' => $branch->id,
            'branch_to' => $branchB->id,
            'mailer_id' => $mailer->id,
            'trackings' => '1234\n3654',
            'reference' => 'The reference update',
            'qty' => 2,
            'status' => 'A',
            'tot_packages' => 2,
            'tot_weight' => 21,
        ]);

        $this->assertDatabaseHas('invoices', [
            "tenant_id" => $tenant->id,
            "warehouse_id" => $tenant->id,
            "updated_by_code" => $admin->id,
            'branch_id' => $branchB->id,
            'client_name' => 'The client of the direct comission',
            'client_email' => 'direct.comission@client.test',
            'status' => 'A',
            'volumetric_weight' => 21,
            'real_weight' => 23,
            'total' => $mailer->real_price * 23,
            'notes' => 'The notes of the invoice updated',
            'manual_id' => 15
        ]);

        tap($branchB->invoices->first()->details->first(), function ($detail) {
            $this->assertEquals($detail->invoice_id, 1);
            $this->assertEquals($detail->qty, 1);
            $this->assertEquals($detail->type, 1);
            $this->assertEquals($detail->length, 10);
            $this->assertEquals($detail->width, 10);
            $this->assertEquals($detail->height, 10);
            $this->assertEquals($detail->vol_weight, 8);
            $this->assertEquals($detail->real_weight, 9);
            $this->assertEquals('4444444', $detail->tracking);
        });

        tap($branchB->invoices->first()->details->last(), function ($detail) {
            $this->assertEquals($detail->invoice_id, 1);
            $this->assertEquals($detail->qty, 1);
            $this->assertEquals($detail->type, 2);
            $this->assertEquals($detail->length, 12);
            $this->assertEquals($detail->width, 12);
            $this->assertEquals($detail->height, 12);
            $this->assertEquals($detail->vol_weight, 13);
            $this->assertEquals($detail->real_weight, 14);
            $this->assertEquals('55555555', $detail->tracking);
        });

        $invoice = $client->fresh()->clientInvoices->first();

        Queue::assertPushed(SendInvoiceCreatedEmail::class, function ($job) use ($invoice) {
            return $job->invoice->id === $invoice->id;
        });
    }

    /** @test */
    public function the_client_does_not_get_an_email_when_his_email_is_the_default_one()
    {
        $this->withoutExceptionHandling();

        Queue::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $mailer = factory(Mailer::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id, ]);

        $this->actingAs($admin);

        \Gate::define('edit-warehouse', function ($admin) {
            return true;
        });

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'email' => $tenant->email_allowed_dup, ]);

        $box = factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);

        $warehouse = $tenant->warehouses()->create([
            'branch_to' => $branchB->id,
            'branch_from' => $branch->id,
            'client_id' => $client->id,
            'mailer_id' => $mailer->id,
            'trackings' => '1234',
            'reference' => 'The reference',
            'qty' => 1,
            'type' => 'A',
        ]);

        $invoice = Invoice::create([
            'tenant_id' => $tenant->id,
            'warehouse_id' => $warehouse->id,
            'client_id' => $client->id,
            'branch_id' => $warehouse->branch_to,
            'created_by_code' => $admin->id,
            'updated_by_code' => $admin->id,
            'client_name' => 'The Name of the client',
            'client_email' => 'email@client.com',
            'volumetric_weight' => 8,
            'real_weight' => 9,
            'total' => $mailer->vol_price * 8,
        ]);

        $detail = $invoice->details()->create([
            'qty' => 1,
            'type' => 1,
            'length' => 10,
            'width' => 10,
            'height' => 10,
            'vol_weight' => 8,
            'real_weight' => 9,
        ]);

        $response = $this->actingAs($admin)->get(route('tenant.warehouse.edit', [$tenant->domain, $warehouse->id]));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.warehouse.edit');
        $response->assertViewHas(['userBranches', 'branches', 'mailers', 'warehouse', ]);

        $response = $this->actingAs($admin)->patch(route('tenant.warehouse.update', [$tenant->domain, $warehouse->id]), [
            'branch_from' => $branch->id,
            'branch_to' => $branchB->id,
            'client_id' => $client->id,
            'mailer_id' => $mailer->id,
            'trackings' => '1234\n3654',
            'reference' => 'The reference update',
            'qty' => 2,
            'type' => 'A',
            'tot_packages' => 2,
            'tot_weight' => 21,

            //
            'client_name' => 'The client of the direct comission',
            'client_email' => 'direct.comission@client.test',
            'total_volumetric_weight' => 21,
            'total_real_weight' => 23,
            'total' => $mailer->real_price * 23,
            'notes' => 'The notes of the invoice updated',
            'invoice_id' => $invoice->id,
            'invoice_detail' => [
                ['qty' => 1, 'type' => 1, 'length' => 10, 'width' => 10, 'height' => 10, 'vol_weight' => 8, 'real_weight' => 9, 'wdid' => $detail->id, ],
                ['qty' => 1, 'type' => 2, 'length' => 12, 'width' => 12, 'height' => 12, 'vol_weight' => 13, 'real_weight' => 14, ],
            ]
        ]);
        $response->assertRedirect(route('tenant.warehouse.edit', [$tenant->domain, 1]));

        $invoice = $client->fresh()->clientInvoices->first();

        Queue::assertNotPushed(SendInvoiceCreatedEmail::class);
    }

    /** @test */
    public function warehouse_invoice_accepts_partial_payment()
    {
        $this->withoutExceptionHandling();

        Queue::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $mailer = factory(Mailer::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id,]);

        $this->actingAs($admin);

        \Gate::define('edit-warehouse', function ($admin) {
            return true;
        });

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, ]);

        factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);

        $warehouse = $tenant->warehouses()->create([
            'branch_to' => $branchB->id,
            'branch_from' => $branch->id,
            'client_id' => $client->id,
            'mailer_id' => $mailer->id,
            'trackings' => '1234',
            'reference' => 'The reference',
            'qty' => 1,
            'type' => 'A',
        ]);

        $invoice = Invoice::create([
            'tenant_id' => $tenant->id,
            'warehouse_id' => $warehouse->id,
            'client_id' => $client->id,
            'branch_id' => $warehouse->branch_to,
            'created_by_code' => $admin->id,
            'updated_by_code' => $admin->id,
            'client_name' => 'The Name of the client',
            'client_email' => 'email@client.com',
            'volumetric_weight' => 8,
            'real_weight' => 9,
            'manual_id' => 9,
            'total' => $mailer->vol_price * 8,
        ]);

        $detail = $invoice->details()->create([
            'qty' => 1,
            'type' => 1,
            'length' => 10,
            'width' => 10,
            'height' => 10,
            'vol_weight' => 8,
            'real_weight' => 9,
        ]);

        $response = $this->actingAs($admin)->get(route('tenant.warehouse.edit', [$tenant->domain, $warehouse->id]));
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->patch(route('tenant.warehouse.update', [$tenant->domain, $warehouse->id]), [
            'branch_from' => $branch->id,
            'branch_to' => $branchB->id,
            'client_id' => $client->id,
            'mailer_id' => $mailer->id,
            'trackings' => '1234\n3654',
            'reference' => 'The reference update',
            'qty' => 2,
            'type' => 'A',
            'tot_packages' => 2,
            'tot_weight' => 21,

            //payment
            'amount_paid' => 30,
            'payment_method' => 1,
            'payment_ref' => 'The client paid $30.00',

            //
            'client_name' => 'The client of the direct comission',
            'client_email' => 'direct.comission@client.test',
            'total_volumetric_weight' => 21,
            'total_real_weight' => 23,
            'total' => $mailer->real_price * 23,
            'notes' => 'The notes of the invoice updated',
            'invoice_id' => $invoice->id,
            'manual_id' => 15, // invoice
            'invoice_detail' => [
                ['qty' => 1, 'type' => 1, 'length' => 10, 'width' => 10, 'height' => 10, 'vol_weight' => 8, 'real_weight' => 9, 'tracking' => '4444444', 'wdid' => $detail->id, ],
                ['qty' => 1, 'type' => 2, 'length' => 12, 'width' => 12, 'height' => 12, 'vol_weight' => 13, 'real_weight' => 14,'trac king' => '55555555',  ],
            ]
        ]);

        $invoice = $client->fresh()->clientInvoices->first();

        $payment = $invoice->payments->first();

        $this->assertNotNull($payment);
        $this->assertEquals($tenant->id, $payment->tenant_id);
        $this->assertEquals($invoice->id, $payment->invoice_id);
        $this->assertEquals('30.0', $payment->amount_paid);
        $this->assertEquals('1', $payment->payment_method);
        $this->assertEquals(1, $payment->is_first);
        $this->assertEquals(date('Y-m-d'), $payment->created_at->format('Y-m-d'));

        Queue::assertPushed(SendInvoiceCreatedEmail::class, function ($job) use ($invoice) {
            return $job->invoice->id === $invoice->id;
        });

        Queue::assertPushed(SendPaymentCreatedEmail::class, function ($job) use ($payment) {
            return $job->payment->id === $payment->id;
        });
    }

    /** @test */
    public function warehouse_invoice_must_have_delivered_trackings_when_payment_is_full()
    {
        $this->withoutExceptionHandling();

        Queue::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $mailer = factory(Mailer::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id,]);

         $this->actingAs($admin);

        \Gate::define('edit-warehouse', function ($admin) {
            return true;
        });

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, ]);

        factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);

        $warehouse = $tenant->warehouses()->create([
            'branch_to' => $branchB->id,
            'branch_from' => $branch->id,
            'client_id' => $client->id,
            'mailer_id' => $mailer->id,
            'trackings' => '1234',
            'reference' => 'The reference',
            'qty' => 1,
            'type' => 'A',
        ]);

        $response = $this->actingAs($admin)->get(route('tenant.warehouse.edit', [$tenant->domain, $warehouse->id]));
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->patch(route('tenant.warehouse.update', [$tenant->domain, $warehouse->id]), [
            'branch_from' => $branch->id,
            'branch_to' => $branchB->id,
            'client_id' => $client->id,
            'mailer_id' => $mailer->id,
            'trackings' => '1234\n3654\n45454545',
            'reference' => 'The reference update',
            'qty' => 2,
            'type' => 'A',
            'tot_packages' => 2,
            'tot_weight' => 21,

            //payment
            'amount_paid' => 30,
            'payment_method' => 1,
            'payment_ref' => 'The client paid all',

            // invoice
            'delivered_trackings' => '3654\n45454545',
            'client_name' => 'The client of the direct comission',
            'client_email' => 'direct.comission@client.test',
            'total_volumetric_weight' => 21,
            'total_real_weight' => 23,
            'total' => 30,
            'notes' => 'The notes of the invoice updated',
            'manual_id' => 15,
            'invoice_detail' => [
                ['qty' => 1, 'type' => 1, 'length' => 10, 'width' => 10, 'height' => 10, 'vol_weight' => 8, 'real_weight' => 9, 'tracking' => '4444444', ],
                ['qty' => 1, 'type' => 2, 'length' => 12, 'width' => 12, 'height' => 12, 'vol_weight' => 13, 'real_weight' => 14,'trac  king' => '55555555',  ],
            ]
        ]);

        $invoice = $client->fresh()->clientInvoices->first();

        $this->assertEquals('3654\n45454545', $invoice->delivered_trackings);
    }
}
