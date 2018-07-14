<?php

namespace Tests\Feature\Tenant\Mailers;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MailerCreationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_like_this_tenant_user()
    {
        // $this->withoutExceptionHandling();
        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.mailer.create', $tenant->domain), []);
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
    }

    /** @test */
    public function mailer_cannot_be_created_with_invalid_inputs()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        $response = $this->actingAs($admin)->post(route('tenant.mailer.store', $tenant->domain), [
            'mailers' => [
                ['description' => '1', 'vol_price' => 'XX', 'real_price' => 'XX', ]
            ],
        ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.mailer.create', $tenant->domain));

        $response->assertSessionHasErrors([
            'mailers.*.name',
            'mailers.*.status',
            'mailers.*.description',
            'mailers.*.vol_price',
            'mailers.*.real_price',
        ]);
    }

    /** @test */
    public function it_successfully_creates_the_mailer()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);

        \Gate::define('create-mailer', function ($admin) {
            return true;
        });

        $response = $this->actingAs($admin)->get(route('tenant.mailer.create', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.mailer.create');

        $response = $this->actingAs($admin)->post(route('tenant.mailer.store', $tenant->domain), [
            'tenant_id' => $tenant->id,
            'mailers' => [
                ['name' => 'The Mailer', 'status' => 'A', 'description' => 'The description of the mailer', 'vol_price' => 1.75, 'real_price' => 2.50, 'is_dhl' => 'Y' ],
            ]
        ]);

        $this->assertDatabaseHas('mailers', [
            "tenant_id" => $tenant->id,
            "created_by_code" => $admin->id,
            "updated_by_code" => null,
            'name' => 'The Mailer',
            'vol_price' => 1.75,
            'real_price' => 2.50,
            'status' => 'A',
            'is_dhl' => true,
        ]);

        $response->assertRedirect(route('tenant.mailer.list', $tenant->domain));
        $response->assertSessionHas(['flash_success']);
    }

    /** @test */
    public function it_successfully_deletes_the_mailer()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);

        $this->actingAs($admin);

        $mailer = $tenant->mailers()->create([
            'name' => 'The mailer',
            'status' => 'A',
        ]);

        $response = $this->delete(route('tenant.mailer.destroy', $tenant->domain), [
            'id' => $mailer->id,
        ], $this->headers());

        $response->assertStatus(200);
        $this->assertCount(0, $tenant->fresh()->mailers);
        $response->assertJson([
            'error' => false,
            'msg' => __("Deleted successfully"),
        ]);
    }
}
