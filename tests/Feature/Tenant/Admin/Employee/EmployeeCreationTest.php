<?php

namespace Tests\Feature\Tenant\Admin\Employee;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Branch;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Logistics\Mail\Tenant\WelcomeEmployeeEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmployeeCreationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_administrator()
    {
        // $this->withoutExceptionHandling();
        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.admin.employee.create'), []);
        $response->assertRedirect(route('tenant.auth.get.login'));
    }

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
        // $this->withoutExceptionHandling();

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
            'branches'
        ]);
    }

    /** @test */
    public function user_email_must_be_unique()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);
        $employee = factory(User::class)->states('employee')->create(['tenant_id' =>$tenant->id, ]);

        $response = $this->actingAs($admin)->post(route('tenant.admin.employee.store'), [
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'email' => $employee->email,
            'password' => 'secret123',
            'type' => 'E',
            'status' => 'L',
            'branches' => [1]
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.admin.employee.create'));
        $response->assertSessionHasErrors('email');
    }


    /** @test */
    public function it_successfully_creates_the_employee()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($admin)->get(route('tenant.admin.employee.create'));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.employee.create');

        $response = $this->actingAs($admin)->post(route('tenant.admin.employee.store'), [
            'first_name' => 'Firstname',
            'last_name' => 'Lastname',
            'email' => 'employee@tenant.test',
            'type' => 'E',
            'status' => 'L',
            'branches' => [$branch->id],
        ]);

        $response->assertRedirect(route('tenant.admin.employee.list'));
        $response->assertSessionHas(['flash_success']);
        $this->assertDatabaseHas('users', [
            'first_name' => 'Firstname',
            'last_name' => 'Lastname',
            'email' => 'employee@tenant.test',
            'type' => 'E',
            'status' => 'L',
            'password' => null,
            'is_main_admin' => false,
        ]);

        $employee = $tenant->employees->where('type', 'E')->fresh()->first();

        $this->assertCount(1, $employee->branches);
    }

    /** @test */
    public function it_successfully_creates_a_main_administrator()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($admin)->get(route('tenant.admin.employee.create'));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.employee.create');

        $response = $this->actingAs($admin)->post(route('tenant.admin.employee.store'), [
            'first_name' => 'Main',
            'last_name' => 'Administrator',
            'email' => 'main-admin@tenant.test',
            'type' => 'A',
            'status' => 'L',
            'branches' => [$branch->id],
            'is_main_admin' => true,
        ]);

        $response->assertRedirect(route('tenant.admin.employee.list'));
        $response->assertSessionHas(['flash_success']);
        $this->assertDatabaseHas('users', [
            'first_name' => 'Main',
            'last_name' => 'Administrator',
            'email' => 'main-admin@tenant.test',
            'type' => 'A',
            'status' => 'L',
            'password' => null,
            'is_main_admin' => true,
        ]);
    }

    /** @test */
    public function admin_can_create_other_admin()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($admin)->post(route('tenant.admin.employee.store'), [
            'first_name' => 'Firstname',
            'last_name' => 'Lastname',
            'email' => 'admin@tenant.test',
            'type' => 'A',
            'status' => 'L',
            'branches' => [$branch->id],
        ]);

        $response->assertRedirect(route('tenant.admin.employee.list'));
        $response->assertSessionHas(['flash_success']);

        $this->assertDatabaseHas('users', [
            'first_name' => 'Firstname',
            'last_name' => 'Lastname',
            'email' => 'admin@tenant.test',
            'type' => 'A',
            'status' => 'L',
            'password' => null,
            'is_main_admin' => false,
        ]);
    }

    /** @test */
    public function employee_was_created_event_fired()
    {
        $this->withoutExceptionHandling();

        Event::fake();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($admin)->post(route('tenant.admin.employee.store'), [
            'first_name' => 'Firstname',
            'last_name' => 'Lastname',
            'email' => 'employee@tenant.test',
            'type' => 'E',
            'status' => 'L',
            'branches' => [$branch->id],
        ]);

        $employee = $tenant->employees->where('type', 'E')->fresh()->first();

        Event::assertDispatched(\Logistics\Events\Tenant\EmployeeWasCreatedEvent::class, function ($event) use ($tenant, $employee) {
            return $event->tenant->id === $tenant->id
                && $event->employee->id === $employee->id;
        });
    }

    /** @test */
    public function the_employee_gets_a_password_creation_link_by_email()
    {
        // $this->withoutExceptionHandling();

        Mail::fake();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($admin)->post(route('tenant.admin.employee.store'), [
            'first_name' => 'Firstname',
            'last_name' => 'Lastname',
            'email' => 'employee@tenant.test',
            'type' => 'E',
            'status' => 'L',
            'branches' => [$branch->id],
        ]);

        $employee = $tenant->employees->where('type', 'E')->fresh()->first();

        Mail::assertSent(WelcomeEmployeeEmail::class, function ($mail) use ($employee) {
            return $mail->hasTo($employee->email);
        });
    }
}
