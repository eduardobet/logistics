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

class PenalizeClientCreationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_employee()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.invoice.show', [$tenant->domain, 1]));
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
    }

    /** @test */
    public function fine_cannot_be_created_with_empty_invoice_id()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        \Gate::define('edit-invoice', function ($admin) {
            return true;
        });

        $response = $this->actingAs($admin)->post(route('tenant.invoice.penalize', [$tenant->domain]), [
        ], $this->headers());

        $response->assertStatus(500);

        $this->assertArraySubset([
            'error' => true,
            'msg' => 'The invoice id field is required.',
        ], $response->json());
    }

    /** @test */
    public function fine_cannot_be_created_with_empty_amount()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        \Gate::define('edit-invoice', function ($admin) {
            return true;
        });

        $response = $this->actingAs($admin)->post(route('tenant.invoice.penalize', [$tenant->domain]), [
            'invoice_id' => 1,
        ], $this->headers());

        $response->assertStatus(500);

        $this->assertArraySubset([
            'error' => true,
            'msg' => 'The fine total field is required.',
        ], $response->json());
    }

    /** @test */
    public function fine_cannot_be_created_with_non_numeric_amount()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        \Gate::define('edit-invoice', function ($admin) {
            return true;
        });


        $response = $this->actingAs($admin)->post(route('tenant.invoice.penalize', [$tenant->domain]), [
            'invoice_id' => 1,
            'fine_total' => 'XXX'
        ], $this->headers());

        $response->assertStatus(500);

        $this->assertArraySubset([
            'error' => true,
            'msg' => 'The fine total must be a number.',
        ], $response->json());
    }
    
    /** @test */
    public function fine_cannot_be_created_with_empty_or_less_than_3_or_more_than_255_fine_ref()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        \Gate::define('edit-invoice', function ($admin) {
            return true;
        });

        $response = $this->actingAs($admin)->post(route('tenant.invoice.penalize', [$tenant->domain, 1]), [
            'invoice_id' => 1,
            'fine_total' => 90,
        ], $this->headers());

        $response->assertStatus(500);

        $this->assertArraySubset([
            'error' => true,
            'msg' => 'The fine ref field is required.',
        ], $response->json());

        $response = $this->actingAs($admin)->post(route('tenant.invoice.penalize', [$tenant->domain, 1]), [
            'invoice_id' => 1,
            'fine_total' => 90,
            'fine_ref' => 'A',

        ], $this->headers());

        $response->assertStatus(500);

        $this->assertArraySubset([
            'error' => true,
            'msg' => 'The fine ref must be between 3 and 255 characters.',
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

        \Gate::define('edit-invoice', function ($admin) {
            return true;
        });

        $response = $this->actingAs($admin)->post(route('tenant.invoice.penalize', [$tenant->domain]), [
            'invoice_id' => $invoice->id+10,
            'fine_total' => 10,
            'fine_ref' => 'The client paid $10.00 in fine',
        ], $this->headers());

        $response->assertStatus(404);
    }
    
    /** @test */
    public function it_successfully_creates_a_fine()
    {
        $this->withoutExceptionHandling();

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

        \Gate::define('edit-invoice', function ($admin) {
            return true;
        });

        $response = $this->actingAs($admin)->post(route('tenant.invoice.penalize', [$tenant->domain]), [
            'invoice_id' => $invoice->id,
            'fine_total' => 10,
            'fine_ref' => 'The client is paying $10 in fine',
        ], $this->headers());

        $invoice = $invoice->fresh();

        $this->assertEquals('110.0', $invoice->total);
        $this->assertEquals('10.0', $invoice->fine_total);
        $this->assertEquals('The client is paying $10 in fine', $invoice->fine_ref);

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'msg' => 'Success',
            'total' => $invoice->total,
            'totalPaid' => $totalPaid = $invoice->payments->fresh()->sum('amount_paid'),
            'pending' => $invoice->total - $totalPaid,
        ]);
    }
}
