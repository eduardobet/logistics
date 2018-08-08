<?php

namespace Tests\Feature\Tenant\Invoice;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Box;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Client;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_employee()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.payment.list', $tenant->domain));
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
    }

    /**
     * @group mysql
     */
    public function employee_can_see_invoice_list()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $employee = factory(User::class)->states('employee')->create(['tenant_id' => $tenant->id, 'status' => 'A', ]);
        $employee->branches()->sync([$branch->id, ]);

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'pay_volume' => true, 'vol_price' => 2.00]);

        $box = factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);

        $invoiceA = $tenant->invoices()->create([
            'branch_id' => $branch->id,
            'client_id' => $client->id,
            'total' => 160,
        ]);

        $detailA = $invoiceA->details()->create([
            'qty' => 1,
            'type' => 1,
            'description' => 'Buying from amazon',
            'id_remote_store' => '122452222',
            'total' => 160,
        ]);

        $paymentA = $invoiceA->payments()->create([
            'tenant_id' => $invoiceA->tenant_id,
            'amount_paid' => 80,
            'payment_method' => 1,
            'payment_ref' => 'The client pays $80.00',
            'is_first' => true,
        ]);

        $invoiceB = $tenant->invoices()->create([
            'branch_id' => $branch->id,
            'client_id' => $client->id,
            'total' => 100,
        ]);

        $detailB = $invoiceB->details()->create([
            'qty' => 1,
            'type' => 1,
            'description' => 'Buying from ebay',
            'id_remote_store' => '558224',
            'total' => 100,
        ]);

        $paymentB = $invoiceB->payments()->create([
            'tenant_id' => $invoiceB->tenant_id,
            'amount_paid' => 60,
            'payment_method' => 2,
            'payment_ref' => 'The client paid $60.00 yesterday',
            'is_first' => true,
        ]);

        \Gate::define('show-invoice', function ($employee) {
            return true;
        });

        $response = $this->actingAs($employee)->get(route('tenant.payment.list', $tenant->domain));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.payment.index');
        $response->assertViewHas(['payments']);
    }
}
