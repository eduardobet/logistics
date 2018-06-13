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
            'pid',
            'email',
            'telephones',
            'position',
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
        $admin->branches()->sync([$branch->id]);

        $response = $this->actingAs($admin)->get(route('tenant.admin.employee.create'));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.employee.create');
        $response->assertViewHas(['positions']);

        $response = $this->actingAs($admin)->post(route('tenant.admin.employee.store'), [
            'first_name' => 'Firstname',
            'last_name' => 'Lastname',
            'email' => 'employee@tenant.test',
            'type' => 'E',
            'status' => 'L',
            'branches' => [$branch->id],
            'pid' => 'PID',
            'telephones' => '555-5555',
            'position' => 1,
        ]);

        $response->assertRedirect(route('tenant.admin.employee.list'));
        $response->assertSessionHas(['flash_success']);
 
        tap($tenant->employees->where('type', 'E')->fresh()->first(), function ($employee) {
            $this->assertEquals($employee->first_name, 'Firstname');
            $this->assertEquals($employee->last_name, 'Lastname');
            $this->assertEquals($employee->email, 'employee@tenant.test');
            $this->assertEquals($employee->type, 'E');
            $this->assertEquals($employee->status, 'L');
            $this->assertNull($employee->password);
            $this->assertEquals($employee->is_main_admin, false);
            $this->assertEquals($employee->full_name, 'Firstname Lastname');
            $this->assertEquals($employee->pid, 'PID');
            $this->assertEquals($employee->telephones, '555-5555');
            $this->assertEquals($employee->position, 1);
            
            $this->assertCount(1, $employee->branches);
        });
    }

    /** @test */
    public function it_successfully_creates_a_main_administrator()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $admin->branches()->sync([$branch->id]);

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
            'pid' => 'PID',
            'telephones' => '555-5555',
            'position' => 1,
        ]);
            
        $response->assertRedirect(route('tenant.admin.employee.list'));
        $response->assertSessionHas(['flash_success']);

        tap($tenant->employees->where('email', 'main-admin@tenant.test')->fresh()->first(), function ($employee) use ($branch) {
            $this->assertEquals($employee->is_main_admin, true);

            $this->assertTrue($employee->branches->contains($branch));
        });
    }

    /** @test */
    public function admin_can_create_other_admin()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $admin->branches()->sync([$branch->id]);

        $response = $this->actingAs($admin)->post(route('tenant.admin.employee.store'), [
            'first_name' => 'Firstname',
            'last_name' => 'Lastname',
            'email' => 'admin@tenant.test',
            'type' => 'A',
            'status' => 'L',
            'branches' => [$branch->id],
            'pid' => 'PID',
            'telephones' => '555-5555',
            'position' => 1,
        ]);

        $response->assertRedirect(route('tenant.admin.employee.list'));
        $response->assertSessionHas(['flash_success']);
    }

    /** @test */
    public function employee_was_created_event_fired()
    {
        $this->withoutExceptionHandling();

        Event::fake();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $admin->branches()->sync([$branch->id]);

        $response = $this->actingAs($admin)->post(route('tenant.admin.employee.store'), [
            'first_name' => 'Firstname',
            'last_name' => 'Lastname',
            'email' => 'employee@tenant.test',
            'type' => 'E',
            'status' => 'L',
            'branches' => [$branch->id],
            'pid' => 'PID',
            'telephones' => '555-5555',
            'position' => 1,
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
        $admin->branches()->sync([$branch->id]);

        $response = $this->actingAs($admin)->post(route('tenant.admin.employee.store'), [
            'first_name' => 'Firstname',
            'last_name' => 'Lastname',
            'email' => 'employee@tenant.test',
            'type' => 'E',
            'status' => 'L',
            'branches' => [$branch->id],
            'pid' => 'PID',
            'telephones' => '555-5555',
            'position' => 1,
        ]);

        $employee = $tenant->employees->where('type', 'E')->fresh()->first();

        Mail::assertSent(WelcomeEmployeeEmail::class, function ($mail) use ($employee) {
            return $mail->hasTo($employee->email);
        });
    }
}
