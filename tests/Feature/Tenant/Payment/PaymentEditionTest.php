<?php

namespace Tests\Feature\Tenant\Payment;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Box;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Client;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentEditionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_employee()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.payment.show', [$tenant->domain, 1]));
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
    }

    /** @test */
    public function it_successfully_edit_payment_method()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id, ]);

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'pay_volume' => true, 'vol_price' => 2.00]);
        factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);

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

        \Gate::define('edit-payment', function ($admin) {
            return true;
        });

        $response = $this->actingAs($admin)->patch(
            route('tenant.payment.update', [$tenant->domain, $payment->id]),
            [
                'payment_id' => $payment->id,
                'payment_method' => 2,
            ],
            $this->headers()
        );

        $payment = $payment->fresh();

        $this->assertEquals(2, $payment->payment_method);

        $response->assertJson([
            'msg' => 'Success',
            'error' => false,
        ]);
    }

    /** @test */
    public function it_successfully_inactivate_the_payment()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id, ]);

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'pay_volume' => true, 'vol_price' => 2.00]);
        factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);

        $invoice = $tenant->invoices()->create([
            'branch_id' => 1,
            'client_id' => 1,
            'total' => 100,
            'is_paid' => true,
        ]);

        $payment = $invoice->payments()->create([
            'tenant_id' => $invoice->tenant_id,
            'amount_paid' => 100,
            'payment_method' => 1,
            'payment_ref' => 'The client paid $100.00',
            'is_first' => true,
            'notes' => 'Payment notes',
        ]);

        \Gate::define('edit-payment', function ($admin) {
            return true;
        });

        $response = $this->actingAs($admin)->patch(
            route('tenant.payment.update', [$tenant->domain, $payment->id]),
            [
                'payment_id' => $payment->id,
                'notes' => 'because i am the brain',
                'status' => 'I',
                'toggling' => 'Y',
            ],
            $this->headers()
        );

        $payment = $payment->fresh();
        $invoice = $invoice->fresh();

        $this->assertEquals('I', $payment->status);
        $this->assertEquals(false, $invoice->is_paid);
        $this->assertEquals('because i am the brain'.PHP_EOL.'Payment notes', $payment->notes);

        $response->assertJson([
            'msg' => 'Success',
            'error' => false,
        ]);
    }

    /** @test */
    public function it_successfully_activate_the_payment()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id, ]);

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'pay_volume' => true, 'vol_price' => 2.00]);
        factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);

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
            'status' => 'I',
        ]);

        \Gate::define('edit-payment', function ($admin) {
            return true;
        });

        $response = $this->actingAs($admin)->patch(
            route('tenant.payment.update', [$tenant->domain, $payment->id]),
            [
                'payment_id' => $payment->id,
                'notes' => 'because i am the brain',
                'status' => 'A',
                'toggling' => 'Y',
            ],
            $this->headers()
        );

        $payment = $payment->fresh();

        $this->assertEquals('A', $payment->status);
        $this->assertEquals('because i am the brain'.PHP_EOL, $payment->notes);

        $response->assertJson([
            'msg' => 'Success',
            'error' => false,
        ]);
    }
}
