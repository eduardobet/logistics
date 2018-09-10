<?php

namespace Tests\Feature\Tenant\Auth;

use Tests\TestCase;
use Logistics\DB\User;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function locked_admin_cannot_logged_in()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, 'status' => 'L']);

        $response = $this->post(route('tenant.auth.post.login', $tenant->domain), ['email' => $admin->email, 'password' => 'secret123']);

        $this->assertTrue($response->isRedirect());
        $response->assertRedirect('auth/login');
        $response->assertSessionHas('errors');
        $this->assertFalse(auth()->check());
    }

    /** @test */
    public function no_one_should_be_able_to_login_to_an_inactive_tenant()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create(['status' => 'I', ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);

        $response = $this->post(route('tenant.auth.post.login', $tenant->domain), ['email' => $admin->email, 'password' => 'secret123']);
        $response->assertStatus(302);
        $response->assertRedirect(route('app.home'));
        $response->assertSessionHas('flash_inactive_tenant');

        $this->assertFalse(auth()->check());
    }

    /** @test */
    public function admin_can_logged_in()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);

        $response = $this->get(route('tenant.auth.get.login', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.auth.login');

        $response = $this->post(route('tenant.auth.post.login', $tenant->domain), ['email' => $admin->email, 'password' => 'secret123']);
        $response->assertRedirect('es/admin/dashboard');

        $this->assertTrue(auth()->check());
    }

    /** @test */
    public function employee_can_logged_in()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $employee = factory(User::class)->states('employee')->create(['tenant_id' =>$tenant->id, ]);

        $response = $this->post(route('tenant.auth.post.login', $tenant->domain), ['email' => $employee->email, 'password' => 'secret123']);
        $response->assertRedirect('es/employee/dashboard');

        $this->assertTrue(auth()->check());
    }

    /** @test */
    public function admin_can_logged_out()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);

        $response = $this->actingAs($admin)->post(route('tenant.auth.post.logout', $tenant->domain));

        $this->assertTrue($response->isRedirect());
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
        $this->assertFalse(auth()->check());
    }

    /** @test */
    public function admin_cannot_logged_in_in_other_tenant()
    {
        // $this->disableExceptionHandling();

        $tenantA = factory(TenantModel::class)->create();

        $tenantB = factory(TenantModel::class)->create(['domain' => 'tenant-b.test', 'name' => 'Tenant B']);
        $adminA = factory(User::class)->states('admin')->create();
        $adminB = factory(User::class)->states('admin')->create();

        $response = $this->post(route('tenant.auth.post.login', $tenantB->domain), ['email' => $adminA->email, 'password' => 'secret123']);
        $this->assertFalse(auth()->check());
        $response->assertRedirect(route('tenant.auth.get.login', $tenantB->domain));
    }

    /** @test */
    public function trottle_is_executed_after_5_unsuccessfull_login()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);

        $response = null;

        for ($i=0; $i < 6; $i++) {
            $response = $this->post(route('tenant.auth.post.login', $tenant->domain), ['email' => 'false-email@example.com', 'password' => 'fakepwd123']);
        }

        $response->assertSessionHas([
            'flash_lock_error'
        ]);
    }
}
