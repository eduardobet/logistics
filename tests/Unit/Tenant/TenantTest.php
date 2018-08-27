<?php

namespace Tests\Unit\Tenant;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use Logistics\Http\Middleware\Tenant;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TenantTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function accessing_a_bad_tenant_host_returns_404()
    {
        // $this->withExceptionHandling();

        $this->expectException(NotFoundHttpException::class);

        $request = $this->get('https://bad-tenant.com');

        $middleware = new Tenant;

        $response = $middleware->handle($request, function ($request) {
        });
    }

    /** @test */
    public function accessing_an_inactive_tenant_redirects_to_app_home()
    {
        // $this->withExceptionHandling();

        $tenant = factory(TenantModel::class)->create(['status' => 'I']);
        $user = factory(\Logistics\DB\User::class)->states('admin')->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user);

        $request = $this->get($tenant->domain);

        $middleware = new Tenant;

        $response = $middleware->handle($request, function ($request) {
        });
        
        $this->assertNotNull($response);
        $this->assertFalse(auth()->check());
        $this->assertEquals('302', $response->getStatusCode());
    }

    /** @test */
    public function accessing_an_active_tenant_host_lets_the_user_continue()
    {
        // $this->withExceptionHandling();

        $tenant = factory(TenantModel::class)->create();

        $request = $this->get($tenant->domain);

        $middleware = new Tenant;
        $response = $middleware->handle($request, function ($request) {
        });

        $this->assertNull($response);
    }

    /** @test */
    public function accessing_an_active_tenant_host_set_global_view_variable_tenant()
    {
        // $this->withExceptionHandling();

        $tenant = factory(TenantModel::class)->create();

        $request = $this->get($tenant->domain);

        $middleware = new Tenant;
        $response = $middleware->handle($request, function ($request) {
        });

        $this->assertNotNull(view()->shared('tenant'));
    }

    /** @test */
    public function a_user_cannot_pretend_to_belong_to_other_tenant()
    {
        $this->withExceptionHandling();

        $tenantA = factory(TenantModel::class)->create();
        $tenantB = factory(TenantModel::class)->create([
            'name' => 'Tenant B',
            'domain' => 'https://tenant-b.test',
            'status' => 'A',
        ]);

        $user = factory(\Logistics\DB\User::class)->states('admin')->create(['tenant_id' => $tenantB->id]);

        $request = $this->actingAs($user)->get($tenantA->domain);

        $middleware = new Tenant;

        $response = $middleware->handle($request, function ($request) {
        });

        $this->assertNotNull($response);
        $this->assertEquals('302', $response->getStatusCode());
    }

    /** @test */
    public function authenticated_user_accessing_an_active_tenant_host_set_global_view_variable_user()
    {
        // $this->withExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $user = factory(\Logistics\DB\User::class)->states('admin')->create(['tenant_id' => $tenant->id]);

        $request = $this->actingAs($user)->get($tenant->domain);

        $middleware = new Tenant;
        $response = $middleware->handle($request, function ($request) {
        });

        $this->assertNotNull(view()->shared('user'));
    }

    /** @test */
    public function it_touches_the_env_file_with_the_right_content()
    {
        $tenant = factory(TenantModel::class)->create();

        $tenant->setConfigs();

        $this->assertEquals(config('app.name'), $tenant->name);
        $this->assertEquals(config('app.url'), $tenant->domain);
        $this->assertEquals(config('app.domain'), $tenant->domain);
        $this->assertEquals(config('app.locale'), $tenant->lang);
        $this->assertEquals(config('app.country'), $tenant->country_id);
        $this->assertEquals(config('app.timezone'), $tenant->timezone);

        $this->assertEquals(config('session.domain'), $tenant->domain);

        $this->assertEquals(config('mail.driver'), $tenant->mail_driver);
        $this->assertEquals(config('mail.host'), $tenant->mail_host);
        $this->assertEquals(config('mail.port'), $tenant->mail_port);
        $this->assertEquals(config('mail.from.address'), $tenant->mail_from_address);
        $this->assertEquals(config('mail.from.name'), $tenant->mail_from_name);
        $this->assertEquals(config('mail.encryption'), $tenant->mail_encryption);
        $this->assertEquals(config('mail.username'), $tenant->mail_username);
        $this->assertEquals(config('mail.password'), $tenant->mail_password);

        $this->assertEquals(config('services.mailgun.domain'), $tenant->mailgun_domain);
        $this->assertEquals(config('services.mailgun.secret'), $tenant->mailgun_secret);
    }
}
