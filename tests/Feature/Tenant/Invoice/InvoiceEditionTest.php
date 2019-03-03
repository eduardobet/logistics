<?php

namespace Tests\Feature\Tenant\Invoice;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Box;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Client;
use Illuminate\Support\Facades\Queue;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Logistics\Jobs\Tenant\SendInvoiceCreatedEmail;

class InvoiceEditionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_employee()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.invoice.edit', [$tenant->domain, 1]));
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
    }

    /** @test */
    public function invoice_cannot_be_edited_with_invalid_inputs()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        $response = $this->actingAs($admin)->patch(route('tenant.invoice.update', [$tenant->domain, 1]), [
            'invoice_detail' => [
                ['qty' => 'XX', 'type' => 'XX', 'description' => '1', 'total' => 'XX', ]
            ],
            'amount_paid' => 'XX',
            'payment_method' => 'XX',
            'payment_ref' => '1',
        ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.invoice.edit', [$tenant->domain, 1]));

        $response->assertSessionHasErrors([
            'client_id',
            'invoice_detail.*.qty',
            'invoice_detail.*.type',
            'invoice_detail.*.description',
            'invoice_detail.*.id_remote_store',
            'invoice_detail.*.total',

            // payment
            'amount_paid',
            'payment_method',
            'payment_ref',
        ]);
    }

    /** @test */
    public function it_successfuly_updates_the_invoice()
    {
        $this->withoutExceptionHandling();

        Queue::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id,]);

        \Gate::define('edit-invoice', function ($admin) {
            return true;
        });

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'pay_volume' => true, 'vol_price' => 2.00 ]);

        $box = factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);

        $invoice = $tenant->invoices()->create([
            'branch_id' => $branch->id,
            'client_id' => $client->id,
            'total' => 160,
            'created_at' => '2017-01-30',
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

        $response = $this->actingAs($admin)->get(route('tenant.invoice.edit', [$tenant->domain, $invoice->id]));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.invoice.edit');
        $response->assertViewHas(['clients', 'invoice',]);

        $response = $this->actingAs($admin)->patch(route('tenant.invoice.update', [$tenant->domain, $invoice->id]), [
            'branch_id' => $branch->id,
            'client_id' => $client->id,
            'total' => 180,
            'created_at' => '2017-02-25',
            'invoice_detail' => [
                ['qty' => 1, 'type' => 1, 'description' => 'Buying from amazon update', 'id_remote_store' => 122452222, 'total' => 100, 'idid' => $detailA->id ],
                ['qty' => 1, 'type' => 2, 'description' => 'Buying from ebay update', 'id_remote_store' => 10448796566, 'total' => 80, 'idid' => $detailB->id],
            ],

            //payment
            'amount_paid' => 90,
            'payment_method' => 1,
            'payment_ref' => 'The client paid $90.00',
        ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.invoice.edit', [$tenant->domain, 1, 'branch_id' => $branch->id, ]));

        $invoice = $invoice->fresh()->first();

        tap($invoice, function ($invoice) use ($tenant, $admin, $client, $payment) {
            $this->assertEquals($tenant->id, $invoice->tenant_id);
            $this->assertEquals($admin->id, $invoice->updated_by_code);
            $this->assertEquals($client->id, $invoice->client_id);
            $this->assertEquals('A', $invoice->status);
            $this->assertEquals('180.0', $invoice->total);
            $this->assertEquals('2017-02-25', $invoice->created_at->format('Y-m-d'));

            $payment = $payment->fresh()->first();
            $this->assertEquals($tenant->id, $payment->tenant_id);
            $this->assertEquals($invoice->id, $payment->invoice_id);
            $this->assertEquals('90.0', $payment->amount_paid);
            $this->assertEquals('1', $payment->payment_method);
            $this->assertEquals('The client paid $90.00', $payment->payment_ref);
        });

        tap($invoice->details->first(), function ($detail) {
            $this->assertEquals($detail->invoice_id, 1);
            $this->assertEquals($detail->qty, 1);
            $this->assertEquals($detail->type, 1);
            $this->assertEquals('Buying from amazon update', $detail->description);
            $this->assertEquals('122452222', $detail->id_remote_store);
            $this->assertEquals('100.0', $detail->total);
        });

        tap($invoice->details->last(), function ($detail) {
            $this->assertEquals($detail->invoice_id, 1);
            $this->assertEquals($detail->qty, 1);
            $this->assertEquals($detail->type, 2);
            $this->assertEquals('Buying from ebay update', $detail->description);
            $this->assertEquals('10448796566', $detail->id_remote_store);
            $this->assertEquals('80.0', $detail->total);
        });
    }

    /** @test */
    public function the_client_receives_an_updated_email_with_his_invoice()
    {
        $this->withoutExceptionHandling();

        Queue::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id,]);

        \Gate::define('edit-invoice', function ($admin) {
            return true;
        });

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'pay_volume' => true, 'vol_price' => 2.00 ]);

        $box = factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);

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

        $response = $this->actingAs($admin)->patch(route('tenant.invoice.update', [$tenant->domain, $invoice->id]), [
            'branch_id' => $branch->id,
            'client_id' => $client->id,
            'total' => 180,
            'invoice_detail' => [
                ['qty' => 1, 'type' => 1, 'description' => 'Buying from amazon', 'id_remote_store' => 122452222, 'total' => 100,  ],
                ['qty' => 1, 'type' => 2, 'description' => 'Buying from ebay', 'id_remote_store' => 10448796566, 'total' => 80, ],
            ],

            //payment
            'amount_paid' => 90,
            'payment_method' => 1,
            'payment_ref' => 'The client paid $90.00',
        ]);

        $invoice = $client->fresh()->clientInvoices->first();

        Queue::assertPushed(SendInvoiceCreatedEmail::class, function ($job) use ($invoice) {
            return $job->invoice->id === $invoice->id;
        });
    }

    /** @test */
    public function the_client_invoice_email_can_be_resent_to_him()
    {
        $this->withoutExceptionHandling();

        Queue::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id, ]);

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'pay_volume' => true, 'vol_price' => 2.00]);

        $box = factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);

        $invoice = $tenant->invoices()->create([
            'branch_id' => $branch->id,
            'client_id' => $client->id,
            'total' => 100,
        ]);

        $detailA = $invoice->details()->create([
            'qty' => 1,
            'type' => 1,
            'description' => 'Buying from amazon',
            'id_remote_store' => '122452222',
            'total' => 100,
        ]);

        $payment = $invoice->payments()->create([
            'tenant_id' => $invoice->tenant_id,
            'amount_paid' => 50,
            'payment_method' => 1,
            'payment_ref' => 'The client paid $80.00',
            'is_first' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('tenant.invoice.invoice.resend', [$tenant->domain, $invoice->id]), [
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'msg' => __("Success"),
        ]);

        Queue::assertPushed(SendInvoiceCreatedEmail::class, function ($job) use ($invoice) {
            return $job->invoice->id === $invoice->id;
        });
    }

    /** @test */
    public function it_successfuly_inactives_the_invoice()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id,]);

        \Gate::define('edit-invoice', function ($admin) {
            return true;
        });

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'pay_volume' => true, 'vol_price' => 2.00 ]);

        $box = factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);

        $invoice = $tenant->invoices()->create([
            'branch_id' => $branch->id,
            'client_id' => $client->id,
            'total' => 160,
        ]);

        $response = $this->actingAs($admin)->get(route('tenant.invoice.edit', [$tenant->domain, $invoice->id]));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.invoice.edit');
        $response->assertViewHas(['clients', 'product_types']);
        
        $response = $this->actingAs($admin)->post(route('tenant.invoice.inactive', [$tenant->domain, $invoice->id]), [
            'invoice_id' => $invoice->id,
        ], $this->headers());
        
        $invoice = $invoice->fresh()->first();
        
        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'msg' => 'Success',
        ]);

        $this->assertEquals('I', $invoice->status);
    }

    /** @test */
    public function the_client_invoice_email_cannot_be_resent_when_his_email_is_the_default_one()
    {
        $this->withoutExceptionHandling();

        Queue::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id, ]);

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'pay_volume' => true, 'vol_price' => 2.00, 'email' => $tenant->email_allowed_dup]);

        $box = factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);

        $invoice = $tenant->invoices()->create([
            'branch_id' => $branch->id,
            'client_id' => $client->id,
            'total' => 100,
        ]);

        $detailA = $invoice->details()->create([
            'qty' => 1,
            'type' => 1,
            'description' => 'Buying from amazon',
            'id_remote_store' => '122452222',
            'total' => 100,
        ]);

        $payment = $invoice->payments()->create([
            'tenant_id' => $invoice->tenant_id,
            'amount_paid' => 50,
            'payment_method' => 1,
            'payment_ref' => 'The client paid $80.00',
            'is_first' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('tenant.invoice.invoice.resend', [$tenant->domain, $invoice->id]), []);

        Queue::assertNotPushed(SendInvoiceCreatedEmail::class);
    }
}
