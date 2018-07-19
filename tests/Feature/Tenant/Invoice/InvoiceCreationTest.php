<?php

namespace Tests\Feature\Tenant\Invoice;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Box;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Client;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
    public function it_successfuly_creates_the_invoice()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);

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
            'branch_id' => $branchB->id,
            'branch_code' => $branchB->code,
        ]);

        $response = $this->actingAs($admin)->get(route('tenant.invoice.create', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.invoice.create');
        $response->assertViewHas(['clients',]);

        $response = $this->actingAs($admin)->post(route('tenant.invoice.store', $tenant->domain), [
            'branch_id' => $branchB->id,
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
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.invoice.edit', [$tenant->domain, 1]));

        tap($client->clientInvoices->first(), function ($invoice) use ($tenant, $admin, $client) {
            $this->assertEquals($tenant->id, $invoice->tenant_id);
            $this->assertEquals($admin->id, $invoice->created_by_code);
            $this->assertEquals($client->id, $invoice->client_id);
            $this->assertEquals('A', $invoice->status);
            $this->assertEquals('160.0', $invoice->total);

            $payment = $invoice->payments->first();
            $this->assertEquals($tenant->id, $payment->tenant_id);
            $this->assertEquals($invoice->id, $payment->invoice_id);
            $this->assertEquals('80.0', $payment->amount_paid);
            $this->assertEquals('1', $payment->payment_method);
            $this->assertEquals('The client paid $80.00', $payment->payment_ref);
        });

        $this->assertCount(1, $branchB->notifications);
    }

    /** @test */
    public function payment_is_not_generated_amount_paid_is_zero()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);

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
            'branch_id' => $branchB->id,
            'branch_code' => $branchB->code,
        ]);

        $response = $this->actingAs($admin)->get(route('tenant.invoice.create', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.invoice.create');
        $response->assertViewHas(['clients',]);

        $response = $this->actingAs($admin)->post(route('tenant.invoice.store', $tenant->domain), [
            'branch_id' => $branchB->id,
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
        $response->assertRedirect(route('tenant.invoice.edit', [$tenant->domain, 1]));

        tap($client->clientInvoices->first(), function ($invoice) use ($tenant, $admin, $client) {
            $this->assertEquals($tenant->id, $invoice->tenant_id);
            $this->assertEquals($admin->id, $invoice->created_by_code);
            $this->assertEquals($client->id, $invoice->client_id);
            $this->assertEquals('A', $invoice->status);
            $this->assertEquals('160.0', $invoice->total);

            $this->assertCount(0, $invoice->payments);
        });

        $this->assertCount(1, $branchB->notifications);
    }

    /** @test */
    public function the_client_receives_an_email_with_his_invoice()
    {
        $this->withoutExceptionHandling();

        Mail::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);

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
            'branch_id' => $branchB->id,
            'branch_code' => $branchB->code,
        ]);

        $response = $this->actingAs($admin)->get(route('tenant.invoice.create', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.invoice.create');
        $response->assertViewHas(['clients',]);

        $response = $this->actingAs($admin)->post(route('tenant.invoice.store', $tenant->domain), [
            'branch_id' => $branchB->id,
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

        Mail::assertQueued(\Logistics\Mail\Tenant\InvoiceCreated::class, function ($mail) use ($client) {
            return $mail->hasTo($client->email) && $mail->invoice->id = 1;
        });
    }
}
