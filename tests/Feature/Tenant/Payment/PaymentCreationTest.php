<?php

namespace Tests\Feature\Tenant\Payment;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Box;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Client;
use Illuminate\Support\Facades\Queue;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Logistics\Jobs\Tenant\SendPaymentCreatedEmail;

class PaymentCreationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_employee()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.payment.create', [$tenant->domain, 1]));
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
    }

    /** @test */
    public function payment_cannot_be_created_with_empty_invoice_id()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        \Gate::define('create-payment', function ($admin) {
            return true;
        });

        $response = $this->actingAs($admin)->post(route('tenant.payment.store', [$tenant->domain, 1]), [
        ], $this->headers());

        $response->assertStatus(500);

        $this->assertArraySubset([
            'error' => true,
            'msg' => 'The invoice id field is required.',
        ], $response->json());
    }

    /** @test */
    public function payment_cannot_be_created_with_empty_amount_paid()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        \Gate::define('create-payment', function ($admin) {
            return true;
        });


        $response = $this->actingAs($admin)->post(route('tenant.payment.store', [$tenant->domain, 1]), [
            'invoice_id' => 1,
        ], $this->headers());

        $response->assertStatus(500);

        $this->assertArraySubset([
            'error' => true,
            'msg' => 'The amount paid field is required.',
        ], $response->json());
    }

    /** @test */
    public function payment_cannot_be_created_with_non_numeric_amount_paid()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        \Gate::define('create-payment', function ($admin) {
            return true;
        });


        $response = $this->actingAs($admin)->post(route('tenant.payment.store', [$tenant->domain]), [
            'invoice_id' => 1,
            'amount_paid' => 'XXX'
        ], $this->headers());

        $response->assertStatus(500);

        $this->assertArraySubset([
            'error' => true,
            'msg' => 'The amount paid must be a number.',
        ], $response->json());
    }

    /** @test */
    public function payment_cannot_be_created_with_amount_paid_greater_than_the_pending()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        $invoice = $tenant->invoices()->create([
            'branch_id' => 1,
            'client_id' => 1,
            'total' => 100,
        ]);

        $payment = $invoice->payments()->create([
            'tenant_id' => $invoice->tenant_id,
            'amount_paid' => 50,
            'payment_method' => 1,
            'payment_ref' => 'The client paid $50.00',
            'is_first' => true,
        ]);

        \Gate::define('create-payment', function ($admin) {
            return true;
        });


        $response = $this->actingAs($admin)->post(route('tenant.payment.store', [$tenant->domain]), [
            'invoice_id' => $invoice->id,
            'amount_paid' => 100,
            'payment_method' => 1,
            'payment_ref' => 'The payment reference',
        ], $this->headers());

        $response->assertStatus(500);

        $this->assertArraySubset([
            'error' => true,
            'msg' => 'The Amount paid must be less than or equal 50.00.',
        ], $response->json());
    }

    /** @test */
    public function payment_cannot_be_created_with_empty_or_non_integer_payment_method()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        \Gate::define('create-payment', function ($admin) {
            return true;
        });

        $response = $this->actingAs($admin)->post(route('tenant.payment.store', [$tenant->domain, 1]), [
            'invoice_id' => 1,
            'amount_paid' => 90
        ], $this->headers());

        $response->assertStatus(500);

        $this->assertArraySubset([
            'error' => true,
            'msg' => 'The payment method field is required.',
        ], $response->json());

        $response = $this->actingAs($admin)->post(route('tenant.payment.store', [$tenant->domain, 1]), [
            'invoice_id' => 1,
            'amount_paid' => 90,
            'payment_method' => 'XX'
        ], $this->headers());

        $response->assertStatus(500);

        $this->assertArraySubset([
            'error' => true,
            'msg' => 'The payment method must be an integer.',
        ], $response->json());
    }

    /** @test */
    public function payment_cannot_be_created_with_empty_or_less_than_3_or_more_than_255_payment_ref()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        \Gate::define('create-payment', function ($admin) {
            return true;
        });

        $response = $this->actingAs($admin)->post(route('tenant.payment.store', [$tenant->domain, 1]), [
            'invoice_id' => 1,
            'amount_paid' => 90,
            'payment_method' => 1,
        ], $this->headers());

        $response->assertStatus(500);

        $this->assertArraySubset([
            'error' => true,
            'msg' => 'The payment ref field is required.',
        ], $response->json());

        $response = $this->actingAs($admin)->post(route('tenant.payment.store', [$tenant->domain, 1]), [
            'invoice_id' => 1,
            'amount_paid' => 90,
            'payment_method' => 1,
            'payment_ref' => 'A',

        ], $this->headers());

        $response->assertStatus(500);

        $this->assertArraySubset([
            'error' => true,
            'msg' => 'The payment ref must be between 3 and 255 characters.',
        ], $response->json());
    }

    /** @test */
    public function error_404_is_shown_if_invoice_does_not_exist()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id,]);

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'pay_volume' => true, 'vol_price' => 2.00 ]);

        $invoice = $tenant->invoices()->create([
            'branch_id' => $branch->id,
            'client_id' => $client->id,
            'total' => 100,
        ]);

        $detail = $invoice->details()->create([
            'qty' => 1,
            'type' => 1,
            'description' => 'Buying from amazon',
            'id_remote_store' => '122452222',
            'total' => 100,
        ]);

        $paymentA = $invoice->payments()->create([
            'tenant_id' => $invoice->tenant_id,
            'amount_paid' => 50,
            'payment_method' => 1,
            'payment_ref' => 'The client paid $50.00',
            'is_first' => true,
        ]);

        \Gate::define('create-payment', function ($admin) {
            return true;
        });


        $response = $this->actingAs($admin)->post(route('tenant.payment.store', [$tenant->domain]), [
            'invoice_id' => $invoice->id+10,
            'amount_paid' => 50,
            'payment_method' => 1,
            'payment_ref' => 'The payment reference',
        ], $this->headers());

        $response->assertStatus(404);
    }
    
    /** @test */
    public function it_successfully_creates_a_payment()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id,]);

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'pay_volume' => true, 'vol_price' => 2.00 ]);
        factory(Box::class)->create([
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

        $invoice->details()->create([
            'qty' => 1,
            'type' => 1,
            'description' => 'Buying from amazon',
            'id_remote_store' => '122452222',
            'total' => 100,
        ]);

        $invoice->payments()->create([
            'tenant_id' => $invoice->tenant_id,
            'amount_paid' => 50,
            'payment_method' => 1,
            'payment_ref' => 'The client paid $50.00',
            'is_first' => true,
        ]);

        \Gate::define('create-payment', function ($admin) {
            return true;
        });

        $response = $this->actingAs($admin)->get(route('tenant.payment.create', [$tenant->domain, $invoice->id]), [
        ], $this->headers());
        
        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'view' => view('tenant.payment.create', ['invoice' => $invoice, 'payments' => $invoice->payments,])->render(),
        ]);

        $response = $this->actingAs($admin)->post(route('tenant.payment.store', [$tenant->domain]), [
            'invoice_id' => $invoice->id,
            'amount_paid' => 50,
            'payment_method' => 1,
            'payment_ref' => 'The payment reference',
            'created_at' => '2017-01-30',
        ], $this->headers());

        $paymentB = $invoice->fresh()->payments->fresh()->where('is_first', false)->first();

        $invoice = $invoice->fresh();

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'msg' => 'Success',
            'pending' => $invoice->total - $invoice->payments->fresh()->sum('amount_paid'),
        ]);

        $this->assertEquals($invoice->id, $paymentB->invoice_id);
        $this->assertEquals('50.0', $paymentB->amount_paid);
        $this->assertEquals('1', $paymentB->payment_method);
        $this->assertEquals('The payment reference', $paymentB->payment_ref);
        $this->assertEquals('0', $paymentB->is_first);
        $this->assertEquals($admin->id, $paymentB->created_by_code);
        $this->assertEquals('2017-01-30', $paymentB->created_at->format('Y-m-d'));

        $this->assertCount(1, $branch->notifications);
    }

    /** @test */
    public function the_invoice_is_paid_when_amount_paid_equals_to_invoice_total()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id,]);

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
            'total' => 100,
        ]);

        $detail = $invoice->details()->create([
            'qty' => 1,
            'type' => 1,
            'description' => 'Buying from amazon',
            'id_remote_store' => '122452222',
            'total' => 100,
        ]);

        $paymentA = $invoice->payments()->create([
            'tenant_id' => $invoice->tenant_id,
            'amount_paid' => 50,
            'payment_method' => 1,
            'payment_ref' => 'The client paid $50.00',
            'is_first' => true,
        ]);

        \Gate::define('create-payment', function ($admin) {
            return true;
        });

        $response = $this->actingAs($admin)->get(route('tenant.payment.create', [$tenant->domain, $invoice->id]), [
        ], $this->headers());
        
        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'view' => view('tenant.payment.create', ['invoice' => $invoice, 'payments' => $invoice->payments,])->render(),
        ]);

        $response = $this->actingAs($admin)->post(route('tenant.payment.store', [$tenant->domain]), [
            'invoice_id' => $invoice->id,
            'amount_paid' => 50,
            'payment_method' => 1,
            'payment_ref' => 'The payment reference',
        ], $this->headers());

        $invoice = $invoice->fresh();
        
        $this->assertEquals(true, (bool)$invoice->is_paid);
    }

    /** @test */
    public function the_warehouse_invoice_should_be_paid_in_totality_in_one_payment()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Other branch']);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id, ]);

        $this->actingAs($admin);

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'pay_volume' => true, 'vol_price' => 2.00]);
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
            'trackings' => '1234',
            'reference' => 'The reference',
            'qty' => 1,
            'type' => 'A',
        ]);

        $invoice = $tenant->invoices()->create([
            'branch_id' => $branch->id,
            'client_id' => $client->id,
            'total' => 100,
            'warehouse_id' => $warehouse->id,
        ]);

        $detail = $invoice->details()->create([
            'qty' => 1,
            'type' => 1,
            'total' => 100,
        ]);
      
        \Gate::define('create-payment', function ($admin) {
            return true;
        });

        $response = $this->actingAs($admin)->post(route('tenant.payment.store', [$tenant->domain]), [
            'invoice_id' => $invoice->id,
            'amount_paid' => 50,
            'payment_method' => 1,
            'payment_ref' => 'The payment reference',
        ], $this->headers());

        $invoice = $invoice->fresh();

        $response->assertStatus(500);

        $this->assertArraySubset([
            'error' => true,
            'msg' => 'The invoice of a warehouse cannot be partially paid.',
        ], $response->json());

        $this->assertEquals(false, (bool)$invoice->is_paid);
    }

    /** @test */
    public function the_client_receives_an_email_with_his_payment_voucher()
    {
        $this->withoutExceptionHandling();

        Queue::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id,]);

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
            'total' => 100,
        ]);

        $detail = $invoice->details()->create([
            'qty' => 1,
            'type' => 1,
            'description' => 'Buying from amazon',
            'id_remote_store' => '122452222',
            'total' => 100,
        ]);

        \Gate::define('create-payment', function ($admin) {
            return true;
        });

        $response = $this->actingAs($admin)->post(route('tenant.payment.store', [$tenant->domain]), [
            'invoice_id' => $invoice->id,
            'amount_paid' => 100,
            'payment_method' => 1,
            'payment_ref' => 'The invoice has been paid.',
        ], $this->headers());

        $invoice = $invoice->fresh();
        $payment = $invoice->fresh()->payments->fresh()->first();

        Queue::assertPushed(SendPaymentCreatedEmail::class, function ($job) use ($tenant, $client, $invoice, $payment) {
            return $job->invoice->id = $invoice->id && $job->payment->id = $payment->id;
        });
    }

    /** @test */
    public function the_client_does_not_receive_an_email_when_his_email_is_default_one()
    {
        $this->withoutExceptionHandling();

        Queue::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id, ]);

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'pay_volume' => true, 'vol_price' => 2.00, 'email' => $tenant->email_allowed_dup, ]);
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

        $detail = $invoice->details()->create([
            'qty' => 1,
            'type' => 1,
            'description' => 'Buying from amazon',
            'id_remote_store' => '122452222',
            'total' => 100,
        ]);

        \Gate::define('create-payment', function ($admin) {
            return true;
        });

        $response = $this->actingAs($admin)->post(route('tenant.payment.store', [$tenant->domain]), [
            'invoice_id' => $invoice->id,
            'amount_paid' => 100,
            'payment_method' => 1,
            'payment_ref' => 'The invoice has been paid.',
        ], $this->headers());

        $invoice = $invoice->fresh();
        $payment = $invoice->fresh()->payments->fresh()->first();

        Queue::assertNotPushed(SendPaymentCreatedEmail::class);
    }
}
