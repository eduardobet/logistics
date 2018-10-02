<?php

namespace Tests\Feature\Tenant\Auth;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Branch;
use Illuminate\Support\Facades\URL;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmployeeActivationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function unknown_employee_should_not_be_able_to_be_unlocked_by_adding_a_password()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $employee = factory(User::class)->states('employee')->create(['tenant_id' => $tenant->id, ]);
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);

        $response = $this->get(route('tenant.employee.get.unlock', [
            $tenant->domain,
            'email' => 'unknown_employee@tenant.test',
            'token' => $employee->token
        ]));

        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
        $response->assertSessionHas('flash_error');
    }

    /** @test */
    public function email_token_password_must_be_present_before_an_account_can_be_able_to_unlock()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $employee = factory(User::class)->states('employee')->create(['tenant_id' => $tenant->id, 'status' => 'L', ]);
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);

        $response = $this->post(route('tenant.employee.post.unlock', [$tenant->domain]));

        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
        $response->assertSessionHas('errors');
        $this->assertDatabaseHas('users', [
            'email' => $employee->email,
            'type' => 'E',
            'status' => 'L',
            'token' => $employee->token,
        ]);
        $this->assertFalse(auth()->check());
    }

    /** @test */
    public function employee_must_be_unlocked_by_adding_a_password()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $employee = factory(User::class)->states('employee')->create(['tenant_id' => $tenant->id, 'status' => 'L', ]);
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);

        $response = $this->get(route('tenant.employee.get.unlock', [$tenant->domain, 'email' => $employee->email, 'token' => $employee->token]));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.auth.unlock');


        $response = $this->post(
            route('tenant.employee.post.unlock', $tenant->domain),
            ['email' => $employee->email, 'password' => 'secrect123', 'token' => $employee->token]
        );
        $response->assertRedirect(route('tenant.employee.dashboard', $tenant->domain));
        $this->assertTrue(auth()->check());

        $this->assertDatabaseHas('users', [
            'email' => $employee->email,
            'type' => 'E',
            'status' => 'A',
            'tenant_id' => $tenant->id,
            'token' => null,
        ]);
    }
}
