<?php

namespace Tests\Feature\Tenant\Admin\Branch;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Branch;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmployeeListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_administrator()
    {
        // $this->withoutExceptionHandling();
        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.admin.employee.list'));
        $response->assertRedirect(route('tenant.auth.get.login'));
    }

    /** @test */
    public function employees_can_only_be_listed_by_authenticated_admin()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);

        $response = $this->get(route('tenant.admin.employee.list'));
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.auth.get.login'));

        $this->assertFalse(auth()->check());
    }

    /** @test */
    public function employee_can_only_be_listed_by_admin()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $employee = factory(User::class)->states('employee')->create(['tenant_id' =>$tenant->id, ]);

        $response = $this->actingAs($employee)->get(route('tenant.admin.employee.list'));
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.auth.get.login'));
        $this->assertFalse(auth()->check());
    }

    /** @test */
    public function it_successfully_lists_the_employees()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $employeeA = factory(User::class)->states('employee')->create(['tenant_id' => $tenant->id]);
        $employeeB = factory(User::class)->states('employee')->create(['tenant_id' => $tenant->id]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);

        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $admin->branches()->sync([$branch->id]);
        $employeeA->branches()->sync([$branch->id]);
        $employeeB->branches()->sync([$branch->id]);

        $response = $this->actingAs($admin)->get(route('tenant.admin.employee.list'));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.employee.index');

        $this->assertTrue($response->original->getData()['employees']->contains($employeeA));
        $this->assertTrue($response->original->getData()['employees']->contains($employeeB));
    }

    /** @test */
    public function admin_can_search_by_employee_name_or_id()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $employeeA = factory(User::class)->states('employee')->create(['tenant_id' => $tenant->id]);
        $employeeB = factory(User::class)->states('employee')->create([
            'tenant_id' => $tenant->id,
            'full_name' => 'Full Name',
        ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);

        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $admin->branches()->sync([$branch->id]);
        $employeeA->branches()->sync([$branch->id]);
        $employeeB->branches()->sync([$branch->id]);

        $response = $this->actingAs($admin)->get(route('tenant.admin.employee.list', [
            'filter' => 'Full'
        ]));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.employee.index');

        $this->assertFalse($response->original->getData()['employees']->contains($employeeA));
        $this->assertTrue($response->original->getData()['employees']->contains($employeeB));
    }
}
