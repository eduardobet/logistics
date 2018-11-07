<?php

namespace Tests\Feature\Tenant\Client;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Box;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Client;
use Illuminate\Support\Facades\job;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Logistics\Jobs\Tenant\SendClientWelcomeEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClientShowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_like_this_tenant_user()
    {
        // $this->withoutExceptionHandling();
        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.client.show', [ $tenant->domain, 1]), []);
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
    }

    /** @test */
    public function it_successfully_shows_the_client()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'email' => 'client@company.com', ]);

        $admin->branches()->sync([$branch->id]);

        \Gate::define('show-client', function ($admin) {
            return true;
        });

        $response = $this->actingAs($admin)->get(route('tenant.client.show', [ $tenant->domain, $client->id]));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.client.show');
        $response->assertViewHas(['client']);
    }
}
