<?php

namespace Tests\Feature\Tenant\Employee;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Box;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Client;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_employee()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.employee.dashboard', $tenant->domain));
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
    }

    /** @test */
    public function admin_and_employee_can_see_their_dashboard()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branchA = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, 'status' => 'L', ]);
        $admin->branches()->sync([$branchA->id]);
        $employee = factory(User::class)->states('employee')->create(['tenant_id' => $tenant->id, 'status' => 'L', ]);
        $employee->branches()->sync([$branchA->id]);

        $response = $this->actingAs($employee)->get(route('tenant.employee.dashboard', $tenant->domain));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.employee.dashboard');

        $response = $this->actingAs($admin)->get(route('tenant.admin.dashboard', $tenant->domain));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.employee.dashboard');
    }

    /** @test */
    public function employee_can_see_total_branches_clients_invoices()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branchA = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, 'status' => 'L', ]);
        $admin->branches()->sync([$branchA->id]);
        $employee = factory(User::class)->states('employee')->create(['tenant_id' => $tenant->id, 'status' => 'L', ]);
        $employee->branches()->sync([$branchA->id]);

        $response = $this->actingAs($employee)->get(route('tenant.employee.dashboard', $tenant->domain));

        $response->assertViewHas(['tot_warehouses', 'tot_clients', 'tot_invoices', ]);
    }

    /** @test */
    public function employee_can_see_last_5_clients()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branchA = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, 'status' => 'L', ]);
        $admin->branches()->sync([$branchA->id]);
        $employee = factory(User::class)->states('employee')->create(['tenant_id' => $tenant->id, 'status' => 'L', ]);
        $employee->branches()->sync([$branchA->id]);

        $response = $this->actingAs($employee)->get(route('tenant.employee.dashboard', $tenant->domain));

        $response->assertViewHas(['last_5_clients', ]);
    }

    /** @test */
    public function employee_can_see_today_earnings()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branchA = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, 'status' => 'L', ]);
        $admin->branches()->sync([$branchA->id]);
        $employee = factory(User::class)->states('employee')->create(['tenant_id' => $tenant->id, 'status' => 'L', ]);
        $employee->branches()->sync([$branchA->id]);

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'pay_volume' => true, 'vol_price' => 2.00]);

        $box = factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branchA->id,
            'branch_code' => $branchA->code,
        ]);

        $invoiceA = $tenant->invoices()->create([
            'branch_id' => $branchA->id,
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
            'branch_id' => $branchA->id,
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

        \DB::update(
            'UPDATE payments SET created_at = ? where id = ?',
            [\Carbon\Carbon::now()->subDay(), $paymentB->id]
        );

        $response = $this->actingAs($employee)->get(route('tenant.employee.dashboard', $tenant->domain));

        $response->assertViewHas(['today_earnings' => 80]);
    }
}
