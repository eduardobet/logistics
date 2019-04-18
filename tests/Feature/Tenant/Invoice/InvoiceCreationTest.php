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

class InvoiceCreationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_employee()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.invoice.create', $tenant->domain));
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
    }

    /** @test */
    public function invoice_cannot_be_created_with_invalid_inputs()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        $response = $this->actingAs($admin)->post(route('tenant.invoice.store', $tenant->domain), [
            'invoice_detail' => [
                ['qty' => 'XX', 'type' => 'XX', 'description' => '1', 'total' => 'XX', ]
            ],
            'amount_paid' => 'XX',
            'payment_method' => 'XX',
            'payment_ref' => '1',
        ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.invoice.create', $tenant->domain));

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
    public function it_validates_the_id_when_manually_provided()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create(['migration_mode' => true, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        $response = $this->actingAs($admin)->post(route('tenant.invoice.store', $tenant->domain), [
            'manual_id' => 'xxx',
        ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.invoice.create', $tenant->domain));

        $response->assertSessionHasErrors([
            'manual_id',
        ]);
    }

    /** @test */
    public function it_successfuly_creates_the_invoice()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id,]);

        \Gate::define('create-invoice', function ($admin) {
            return true;
        });

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'pay_volume' => true, 'vol_price' => 2.00 ]);

        $box = factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);

        $response = $this->actingAs($admin)->get(route('tenant.invoice.create', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.invoice.create');
        $response->assertViewHas(['clients', 'product_types']);

        $response = $this->actingAs($admin)->post(route('tenant.invoice.store', $tenant->domain), [
            'branch_id' => $branch->id,
            'client_id' => $client->id,
            'total' => 160,
            'manual_id' => 14,
            'created_at' => '2017-01-30',
            'invoice_detail' => [
                ['qty' => 1, 'type' => 1, 'description' => 'Buying from amazon', 'id_remote_store' => 122452222, 'total' => 100,  ],
                ['qty' => 1, 'type' => 2, 'description' => 'Buying from ebay', 'id_remote_store' => 10448796566, 'total' => 60, ],
            ],

            //payment
            'amount_paid' => 80,
            'payment_method' => 1,
            'payment_ref' => 'The client paid $80.00',
        ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.invoice.edit', [$tenant->domain, 1, 'branch_id' => $branch->id, ]));

        $invoice = $client->clientInvoices->first();

        tap($invoice, function ($invoice) use ($tenant, $admin, $client) {
            $this->assertEquals($tenant->id, $invoice->tenant_id);
            $this->assertEquals($admin->id, $invoice->created_by_code);
            $this->assertEquals($client->id, $invoice->client_id);
            $this->assertEquals('A', $invoice->status);
            $this->assertEquals(14, $invoice->manual_id);
            $this->assertEquals('160.0', $invoice->total);
            $this->assertEquals('2017-01-30', $invoice->created_at->format('Y-m-d'));
            $this->assertEquals('2017-02-06', $invoice->due_at->format('Y-m-d'));

            $payment = $invoice->payments->first();
            $this->assertEquals($tenant->id, $payment->tenant_id);
            $this->assertEquals($invoice->id, $payment->invoice_id);
            $this->assertEquals('80.0', $payment->amount_paid);
            $this->assertEquals('1', $payment->payment_method);
            $this->assertEquals('The client paid $80.00', $payment->payment_ref);
            $this->assertEquals('2017-01-30', $payment->created_at->format('Y-m-d'));
        });

        tap($invoice->details->first(), function ($detail) {
            $this->assertEquals($detail->invoice_id, 1);
            $this->assertEquals($detail->qty, 1);
            $this->assertEquals($detail->type, 1);
            $this->assertEquals('Buying from amazon', $detail->description);
            $this->assertEquals('122452222', $detail->id_remote_store);
            $this->assertEquals('100.0', $detail->total);
        });

        tap($invoice->details->last(), function ($detail) {
            $this->assertEquals($detail->invoice_id, 1);
            $this->assertEquals($detail->qty, 1);
            $this->assertEquals($detail->type, 2);
            $this->assertEquals('Buying from ebay', $detail->description);
            $this->assertEquals('10448796566', $detail->id_remote_store);
            $this->assertEquals('60.0', $detail->total);
        });

        $this->assertCount(1, $branch->notifications);
    }

    /** @test */
    public function payment_is_not_generated_if_amount_paid_is_zero()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id,]);

        \Gate::define('create-invoice', function ($admin) {
            return true;
        });

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'pay_volume' => true, 'vol_price' => 2.00 ]);

        $box = factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);

        $response = $this->actingAs($admin)->get(route('tenant.invoice.create', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.invoice.create');
        $response->assertViewHas(['clients',]);

        $response = $this->actingAs($admin)->post(route('tenant.invoice.store', $tenant->domain), [
            'branch_id' => $branch->id,
            'client_id' => $client->id,
            'total' => 160,
            'invoice_detail' => [
                ['qty' => 1, 'type' => 1, 'description' => 'Buying from amazon', 'id_remote_store' => 122452222, 'total' => 100,  ],
                ['qty' => 1, 'type' => 2, 'description' => 'Buying from ebay', 'id_remote_store' => 10448796566, 'total' => 60, ],
            ],

            //payment
            'amount_paid' => 0,
        ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.invoice.edit', [$tenant->domain, 1, 'branch_id' => $branch->id, ]));

        tap($client->clientInvoices->first(), function ($invoice) use ($tenant, $admin, $client) {
            $this->assertEquals($tenant->id, $invoice->tenant_id);
            $this->assertEquals($admin->id, $invoice->created_by_code);
            $this->assertEquals($client->id, $invoice->client_id);
            $this->assertEquals('A', $invoice->status);
            $this->assertEquals('160.0', $invoice->total);

            $this->assertCount(0, $invoice->payments);
        });

        $this->assertCount(1, $branch->notifications);
    }

    /** @test */
    public function the_client_receives_an_email_with_his_invoice()
    {
        $this->withoutExceptionHandling();

        Queue::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id,]);

        \Gate::define('create-invoice', function ($admin) {
            return true;
        });

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'pay_volume' => true, 'vol_price' => 2.00 ]);

        $box = factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);

        $response = $this->actingAs($admin)->get(route('tenant.invoice.create', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.invoice.create');
        $response->assertViewHas(['clients',]);

        $response = $this->actingAs($admin)->post(route('tenant.invoice.store', $tenant->domain), [
            'branch_id' => $branch->id,
            'client_id' => $client->id,
            'total' => 160,
            'invoice_detail' => [
                ['qty' => 1, 'type' => 1, 'description' => 'Buying from amazon', 'id_remote_store' => 122452222, 'total' => 100,  ],
                ['qty' => 1, 'type' => 2, 'description' => 'Buying from ebay', 'id_remote_store' => 10448796566, 'total' => 60, ],
            ],

            //payment
            'amount_paid' => 80,
            'payment_method' => 1,
            'payment_ref' => 'The client paid $80.00',
        ]);

        $invoice = $client->fresh()->clientInvoices->first();

        Queue::assertPushed(SendInvoiceCreatedEmail::class, function ($job) use ($invoice) {
            return $job->invoice->id === $invoice->id;
        });
    }

    /** @test */
    public function the_client_does_not_receive_an_email_when_his_email_is_the_default_one()
    {
        $this->withoutExceptionHandling();

        Queue::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id, ]);

        \Gate::define('create-invoice', function ($admin) {
            return true;
        });

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'pay_volume' => true, 'vol_price' => 2.00, 'email' => $tenant->email_allowed_dup]);

        $box = factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);

        $response = $this->actingAs($admin)->get(route('tenant.invoice.create', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.invoice.create');
        $response->assertViewHas(['clients', ]);

        $response = $this->actingAs($admin)->post(route('tenant.invoice.store', $tenant->domain), [
            'branch_id' => $branch->id,
            'client_id' => $client->id,
            'total' => 160,
            'invoice_detail' => [
                ['qty' => 1, 'type' => 1, 'description' => 'Buying from amazon', 'id_remote_store' => 122452222, 'total' => 100, ],
                ['qty' => 1, 'type' => 2, 'description' => 'Buying from ebay', 'id_remote_store' => 10448796566, 'total' => 60, ],
            ],

            //payment
            'amount_paid' => 80,
            'payment_method' => 1,
            'payment_ref' => 'The client paid $80.00',
        ]);

        $invoice = $client->fresh()->clientInvoices->first();

        Queue::assertNotPushed(SendInvoiceCreatedEmail::class);
    }

    /** @test */
    public function invoice_is_marked_paid_if_amount_paid_equals_invoice_amount()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id,]);

        \Gate::define('create-invoice', function ($admin) {
            return true;
        });

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'pay_volume' => true, 'vol_price' => 2.00]);

        factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);

        $response = $this->actingAs($admin)->get(route('tenant.invoice.create', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.invoice.create');
        $response->assertViewHas(['clients', 'product_types']);

        $response = $this->actingAs($admin)->post(route('tenant.invoice.store', $tenant->domain), [
            'branch_id' => $branch->id,
            'client_id' => $client->id,
            'total' => 171.71,
            'manual_id' => 14,
            'created_at' => '2017-01-30',
            'invoice_detail' => [
                ['qty' => 1, 'type' => 1, 'description' => 'Buying from amazon', 'id_remote_store' => 122452222, 'total' => 166.71,  ],
                ['qty' => 1, 'type' => 2, 'description' => 'Uso de TC', 'id_remote_store' => 0, 'total' => 5, ],
            ],

            //payment
            'amount_paid' => 171.71,
            'payment_method' => 1,
            'payment_ref' => 'The invoice is completely paid.',
        ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.invoice.edit', [$tenant->domain, 1, 'branch_id' => $branch->id, ]));

        $invoice = $client->clientInvoices->first();

        tap($invoice, function ($invoice) use ($tenant, $admin, $client) {
            $this->assertEquals('171.71', $invoice->total);
            $this->assertEquals('2017-01-30', $invoice->created_at->format('Y-m-d'));
            $this->assertTrue($invoice->is_paid);

            $payment = $invoice->payments->first();
            $this->assertEquals( '171.71', $payment->amount_paid);
            $this->assertEquals('1', $payment->payment_method);
            $this->assertEquals('The invoice is completely paid.', $payment->payment_ref);
            $this->assertEquals('2017-01-30', $payment->created_at->format('Y-m-d'));
        });
    }
}
