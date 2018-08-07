<?php

namespace Tests\Feature\Tenant\Mailers;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Mailer;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MailerListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_like_this_tenant_user()
    {
        // $this->withoutExceptionHandling();
        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.mailer.list', $tenant->domain));
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
    }


    /** @test */
    public function it_successfully_lists_the_mailers()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $mailerA = factory(Mailer::class)->create(['tenant_id' => $tenant->id]);
        $mailerB = factory(Mailer::class)->create(['tenant_id' => $tenant->id]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $admin->branches()->sync([$branch->id]);

        \Gate::define('show-mailer', function ($admin) {
            return true;
        });

        $response = $this->actingAs($admin)->get(route('tenant.mailer.list', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.mailer.index');

        $this->assertTrue($response->original->getData()['mailers']->contains($mailerA));
        $this->assertTrue($response->original->getData()['mailers']->contains($mailerB));
    }

    /** @test */
    public function admin_can_search_by_employee_name_or_id()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $mailerA = factory(Mailer::class)->create(['tenant_id' => $tenant->id]);
        $mailerB = factory(Mailer::class)->create(['tenant_id' => $tenant->id, 'name' => 'Other Mailer']);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $admin->branches()->sync([$branch->id]);

        \Gate::define('show-mailer', function ($admin) {
            return true;
        });

        $response = $this->actingAs($admin)->get(route('tenant.mailer.list', [
            $tenant->domain,
            'filter' => 'The Mailer'
        ]));

        $this->assertTrue($response->original->getData()['mailers']->contains($mailerA));
        $this->assertFalse($response->original->getData()['mailers']->contains($mailerB));
    }
}
