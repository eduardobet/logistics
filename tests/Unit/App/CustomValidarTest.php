<?php

namespace Tests\Unit\App;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Tenant;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomValidarTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function validate_invalid_password()
    {
        $rules = [
            'password' => 'alpha_num_pwd'
        ];

        $data = [
            'password' => '12346',
        ];

        $v = $this->app['validator']->make($data, $rules);
        $this->assertTrue($v->fails());
    }

    /** @test */
    public function validate_valid_password()
    {
        $rules = [
            'password' => 'alpha_num_pwd'
        ];

        $data = [
            'password' => 'letters123',
        ];

        $v = $this->app['validator']->make($data, $rules);
        $this->assertTrue($v->passes());
    }

    /** @test */
    public function validate_invalid_phones()
    {
        $rules = [
            'phones' => 'mass_phone'
        ];

        $data = [
            'phones' => '123,123',
        ];

        $v = $this->app['validator']->make($data, $rules);
        $this->assertTrue($v->fails());
    }

    /** @test */
    public function validate_valid_phones()
    {
        $rules = ['phones' => 'mass_phone'];
        $data = ['phones' => '123-1234'];

        $v = $this->app['validator']->make($data, $rules);
        $this->assertTrue($v->passes());

        $rules = ['phones' => 'mass_phone'];
        $data = ['phones' => '(507) 123-1234'];

        $v = $this->app['validator']->make($data, $rules);
        $this->assertTrue($v->passes());

        $rules = ['phones' => 'mass_phone'];
        $data = ['phones' => '(507) 123.1234'];

        $v = $this->app['validator']->make($data, $rules);
        $this->assertTrue($v->passes());

        $rules = ['phones' => 'mass_phone'];
        $data = ['phones' => '(507) 123 1234'];

        $v = $this->app['validator']->make($data, $rules);
        $this->assertTrue($v->passes());

        $rules = ['phones' => 'mass_phone'];
        $data = ['phones' => '(507) 6767.1234'];

        $v = $this->app['validator']->make($data, $rules);
        $this->assertTrue($v->passes());

        $rules = ['phones' => 'mass_phone'];
        $data = ['phones' => '(509) 4785.1234'];

        $v = $this->app['validator']->make($data, $rules);
        $this->assertTrue($v->passes());
    }

    /** @test */
    public function validate_invalid_emails()
    {
        $rules = ['emails' => 'mass_email', ];

        $data = ['emails' => '123', ];

        $v = $this->app['validator']->make($data, $rules);
        $this->assertTrue($v->fails());
    }

    /** @test */
    public function validate_valid_emails()
    {
        $rules = ['emails' => 'mass_email'];
        $data = ['emails' => 'valid@company.com'];

        $v = $this->app['validator']->make($data, $rules);
        $this->assertTrue($v->passes());
    }

    /** @test */
    public function validate_user_status_cannot_be_updated_from_locked_to_active()
    {
        $tenant = factory(Tenant::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, 'status' => 'L', ]);

        $rules = [
            'status' => 'user_status:email'
        ];

        $data = [
            'status' => 'A',
            'email' => $admin->email,
        ];

        $v = $this->app['validator']->make($data, $rules);
        $this->assertTrue($v->fails());
    }

    /** @test */
    public function validates_field_not_present()
    {
        $this->withoutExceptionHandling();

        $rules = [
            'email' => 'not_present'
        ];

        $data = [
            'email' => 'email@test.com',
        ];

        $v = $this->app['validator']->make($data, $rules);
        $this->assertTrue($v->fails());
    }

    /** @test */
    public function validates_current_password()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(Tenant::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, 'status' => 'L', ]);

        $this->actingAs($admin);

        $rules = [
            'current_password' => 'pass_check'
        ];

        $data = [
            'current_password' => '1234',
        ];

        $v = $this->app['validator']->make($data, $rules);
        $this->assertTrue($v->fails());
    }
}
