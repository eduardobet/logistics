<?php

namespace Tests\Feature\Tenant\Auth;

use Tests\TestCase;
use Logistics\DB\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Notification;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Logistics\Notifications\Tenant\ResetPwdNotification;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function tenant_user_can_see_link_request_form()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.user.password.request'));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.auth.email');
    }

    /** @test */
    public function unknown_tenant_user_gets_redirected()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();

        $response = $this->from(route('tenant.user.password.request'))
            ->post(route('tenant.user.password.email'), ['email' => 'invalid.tenant-user@gmail.com']);

        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.user.password.request'));
    }

    /** @test */
    public function tenant_user_can_see_reset_form()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $response = $this->get(URL::signedRoute('tenant.user.password.reset', ['token' => 'tenant-user-password-reset-token']));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.auth.reset');
    }

    /** @test */
    public function tenant_user_can_reset_his_password()
    {
        // $this->withoutExceptionHandling();

        Notification::fake();
        $tenant = factory(TenantModel::class)->create();

        $user = factory(User::class)->states('employee')->create(['status' => 'A', 'tenant_id' => $tenant->id, ]);
        $token = '';

        $response = $this->post(route('tenant.user.password.email'), ['email' => $user->email]);

        Notification::assertSentTo(
            $user,
            ResetPwdNotification::class,
            function ($notification, $channels) use (&$token, $user) {
                $token = $notification->token;
                $notification->toMail($user);
                return true;
            }
        );

        $response = $this->post(
            route('tenant.user.password.post.reset'),
            [
                'email' => $user->email,
                'token' => $token,
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
            ]
        );

        $this->assertTrue(auth()->check());
        $response->assertStatus(302);
        $response->assertRedirect('/en/employee/dashboard');
    }

    /** @test */
    public function reset_data_is_being_validated()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();

        $response = $this->post(
            route('tenant.user.password.post.reset'),
            []
        );

        $this->assertFalse(auth()->check());
        $response->assertStatus(302);
    }
}
