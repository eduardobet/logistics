<?php

namespace Tests\Feature\Tenant\Employee;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Branch;
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
}
