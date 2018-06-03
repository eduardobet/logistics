<?php

namespace Tests\Feature\Tenant\Admin;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Branch;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmployeeEditionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_administrator()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.admin.employee.edit', [1]), []);
        $response->assertRedirect(route('tenant.auth.get.login'));
    }

    /** @test */
    public function admin_cannot_update_the_employee_from_locked_to_active()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $employee = factory(User::class)->states('employee')->create(['tenant_id' => $tenant->id, 'status' => 'L', ]);
        $employee->branches()->sync([$branch->id]);

        $response = $this->actingAs($admin)->post(route('tenant.admin.employee.update', ['id' => $employee->id]), [
            'first_name' => 'Employee f name update',
            'last_name' => 'Employee l name update',
            'email' => $employee->email,
            'type' => 'E',
            'status' => 'A',
            'branches' => [$branch->id],
            '_method' => 'PATCH',
        ]);

        $response->assertRedirect(route('tenant.admin.employee.edit', ['id' => $employee->id]));
        $response->assertSessionHasErrors(['status']);
        $this->assertDatabaseMissing('users', [
            'first_name' => 'Employee f name update',
            'last_name' => 'Employee l name update',
            'type' => 'E',
            'status' => 'L',
            'email' => $employee->email,
        ]);
    }

    /** @test */
    public function admin_can_update_the_employee()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $branchA = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch Name B',]);
        $employee = factory(User::class)->states('employee')->create(['tenant_id' => $tenant->id, ]);
        $employee->branches()->sync([$branchA->id]);

        $response = $this->actingAs($admin)->get(route('tenant.admin.employee.edit', ['id' => $employee->id]));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.employee.edit');

        $response = $this->actingAs($admin)->post(route('tenant.admin.employee.update'), [
            'id' => $employee->id,
            'first_name' => 'Employee f name update',
            'last_name' => 'Employee l name update',
            'email' => $employee->email,
            'type' => 'A',
            'status' => 'A',
            'branches' => [$branchB->id],
            '_method' => 'PATCH',
        ]);

        $this->assertDatabaseHas('users', [
            'first_name' => 'Employee f name update',
            'last_name' => 'Employee l name update',
            'type' => 'A',
            'status' => 'A',
            'email' => $employee->email,
        ]);
            
        $response->assertSessionHas(['flash_success']);
        $response->assertRedirect(route('tenant.admin.employee.list'));
        $employee = $tenant->employees->where('email', $employee->email)->fresh()->first();

        $this->assertTrue($employee->branches->contains($branchB));
        $this->assertFalse($employee->branches->contains($branchA));
    }

    /** @test */
    public function admin_can_change_the_employee_to_main_admin()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $branchA = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch Name B', ]);
        $employee = factory(User::class)->states('employee')->create(['tenant_id' => $tenant->id, ]);
        $employee->branches()->sync([$branchA->id]);

        $response = $this->actingAs($admin)->get(route('tenant.admin.employee.edit', ['id' => $employee->id]));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.employee.edit');

        $response = $this->actingAs($admin)->post(route('tenant.admin.employee.update'), [
            'id' => $employee->id,
            'first_name' => 'The main',
            'last_name' => 'Administrator',
            'email' => $employee->email,
            'type' => 'A',
            'status' => 'A',
            'branches' => [$branchB->id],
            'is_main_admin' => true,
            '_method' => 'PATCH',
        ]);

        $this->assertDatabaseHas('users', [
            'first_name' => 'The main',
            'last_name' => 'Administrator',
            'type' => 'A',
            'status' => 'A',
            'is_main_admin' => true,
            'email' => $employee->email,
        ]);
    }

    /** @test */
    public function admin_can_lock_or_inactive_the_employee()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $employee = factory(User::class)->states('employee')->create(['tenant_id' => $tenant->id, ]);
        $employee->branches()->sync([$branch->id]);

        $response = $this->actingAs($admin)->post(route('tenant.admin.employee.update'), [
            'id' => $employee->id,
            'first_name' => 'Employee f name update',
            'last_name' => 'Employee l name update',
            'email' => $employee->email,
            'type' => 'E',
            'status' => 'L',
            'branches' => [$branch->id],
            '_method' => 'PATCH',
        ]);

        $this->assertDatabaseHas('users', [
            'type' => 'E',
            'status' => 'L',
            'email' => $employee->email,
        ]);

        $response->assertSessionHas(['flash_success']);
        $response->assertRedirect(route('tenant.admin.employee.list'));
    }
}
