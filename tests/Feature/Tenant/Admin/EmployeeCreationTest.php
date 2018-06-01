<?php

namespace Tests\Feature\Tenant\Admin;

use Tests\TestCase;
use Logistics\DB\Tenant\User;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmployeeCreationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function employee_can_only_be_created_by_authenticated_admin()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);

        $response = $this->get(route('tenant.admin.employee.create'), []);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.auth.get.login'));

        $this->assertFalse(auth()->check());
    }

    /** @test */
    public function employee_can_only_be_created_by_admin()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $employee = factory(User::class)->states('employee')->create(['tenant_id' =>$tenant->id, ]);

        $response = $this->actingAs($employee)->get(route('tenant.admin.employee.create'), []);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.auth.get.login'));
        $this->assertFalse(auth()->check());
    }

    /** @test */
    public function employee_cannot_be_created_with_invalid_inputs()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);

        $response = $this->actingAs($admin)->post(route('tenant.admin.employee.store'), []);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.admin.employee.create'));

        $response->assertSessionHasErrors([
            'first_name',
            'last_name',
            'email',
            'type',
            'status',
            'tenant_id',
            'branches'
        ]);
    }
}
