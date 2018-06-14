<?php

namespace Tests\Feature\Tenant\Admin\Employee;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Permission;
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
    public function employee_cannot_be_updated_with_invalid_inputs()
    {
        //$this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $permissionA = factory(Permission::class)->create(['tenant_id' => $tenant->id, ]);
        $permissionB = factory(Permission::class)->create(['tenant_id' => $tenant->id, 'name' => 'Perm Name B', 'slug' => 'p-b']);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $branchA = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch Name B', ]);
        $employee = factory(User::class)->states('employee')->create(['tenant_id' => $tenant->id, ]);
        $employee->branches()->sync([$branchA->id]);
        $admin->branches()->sync([$branchA->id]);

        $response = $this->actingAs($admin)->patch(route('tenant.admin.employee.update'), [
            'id' => $employee->id,
            'permissions' => 'XXX',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.admin.employee.edit', $employee->id));

        $response->assertSessionHasErrors([
            'first_name',
            'last_name',
            'pid',
            'email',
            'telephones',
            'position',
            'type',
            'status',
            'branches',
            'permissions',
        ]);
    }

    /** @test */
    public function admin_can_update_the_employee()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $permissionA = factory(Permission::class)->create(['tenant_id' => $tenant->id, ]);
        $permissionB = factory(Permission::class)->create(['tenant_id' => $tenant->id, 'name' => 'Perm Name B', 'slug' => 'p-b' ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $branchA = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch Name B',]);
        $employee = factory(User::class)->states('employee')->create(['tenant_id' => $tenant->id, ]);
        $employee->branches()->sync([$branchA->id]);
        $admin->branches()->sync([$branchA->id]);

        $response = $this->actingAs($admin)->get(route('tenant.admin.employee.edit', ['id' => $employee->id]));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.employee.edit');
        $response->assertViewHas('positions');
        $response->assertViewHas('permissions');

        $response = $this->actingAs($admin)->post(route('tenant.admin.employee.update'), [
            'id' => $employee->id,
            'first_name' => 'Employee f name update',
            'last_name' => 'Employee l name update',
            'email' => $employee->email,
            'type' => 'A',
            'status' => 'A',
            'branches' => [$branchB->id],
            'pid' => 'PID',
            'telephones' => '555-5555',
            'position' => 1,
            'address' => 'In the middle of nowhere',
            'notes' => 'Some notes about the employee',
            'permissions' => [$permissionA->slug, $permissionB->slug],
            '_method' => 'PATCH',
        ]);

        $response->assertSessionHas(['flash_success']);
        $response->assertRedirect(route('tenant.admin.employee.list'));
        $employee = $tenant->employees->where('email', $employee->email)->fresh()->first();

        tap($employee, function($employee) use($tenant, $admin, $permissionA, $permissionB) {
            $this->assertEquals($employee->first_name, 'Employee f name update');
            $this->assertEquals($employee->last_name  ,'Employee l name update');
            $this->assertEquals($employee->type  ,'A');
            $this->assertEquals($employee->status  ,'A');
            $this->assertEquals($employee->full_name  ,'Employee f name update Employee l name update');
            $this->assertEquals($employee->pid  ,'PID');
            $this->assertEquals($employee->telephones  ,'555-5555');
            $this->assertEquals($employee->position, 1);
            $this->assertEquals($employee->address  ,'In the middle of nowhere');
            $this->assertEquals($employee->notes  ,'Some notes about the employee');
            $this->assertEquals($employee->tenant_id, $tenant->id);
            $this->assertNull($employee->created_by_code);
            $this->assertEquals($employee->updated_by_code, $admin->id);

            $this->assertEquals($employee->permissions, [$permissionA->slug, $permissionB->slug]);
        });

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
        $admin->branches()->sync([$branchA->id]);

        $response = $this->actingAs($admin)->post(route('tenant.admin.employee.update'), [
            'id' => $employee->id,
            'first_name' => 'The main',
            'last_name' => 'Administrator',
            'email' => $employee->email,
            'type' => 'A',
            'status' => 'A',
            'branches' => [$branchB->id],
            'is_main_admin' => true,
            'pid' => 'PID',
            'telephones' => '555-5555',
            'position' => 1,
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
        $admin->branches()->sync([$branch->id]);

        $response = $this->actingAs($admin)->post(route('tenant.admin.employee.update'), [
            'id' => $employee->id,
            'first_name' => 'Employee f name update',
            'last_name' => 'Employee l name update',
            'email' => $employee->email,
            'type' => 'E',
            'status' => 'L',
            'branches' => [$branch->id],
            'pid' => 'PID',
            'telephones' => '555-5555',
            'position' => 1,
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
