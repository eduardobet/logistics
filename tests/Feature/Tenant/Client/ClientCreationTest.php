<?php

namespace Tests\Feature\Tenant\Client;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Branch;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClientCreationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_like_this_tenant_user()
    {
        // $this->withoutExceptionHandling();
        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.client.create'), []);
        $response->assertRedirect(route('tenant.auth.get.login'));
    }

    /** @test */
    public function client_cannot_be_created_with_invalid_inputs()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        $response = $this->actingAs($admin)->post(route('tenant.client.store'), []);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.client.create'));

        $response->assertSessionHasErrors([
            'first_name',
            'last_name',
            'pid',
            'email',
            'telephones',
            'type',
            'status',
            'branch_id',
        ]);
    }

    /** @test */
    public function it_successfully_creates_the_client()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        $response = $this->actingAs($admin)->get(route('tenant.client.create'));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.client.create');

        $response = $this->actingAs($admin)->post(route('tenant.client.store'), [
            'first_name' => 'The',
            'last_name' => 'Client',
            'pid' => 'E-8-124926',
            'email' => 'client@company.com',
            'telephones' => '555-5555, 565-5425',
            'type' => 'E',
            'org_name' => 'The Org Name',
            'status' => 'A',
            'branch_id' => 1,
        ]);

        $this->assertDatabaseHas('clients', [
            "tenant_id" => $tenant->id,
            "created_by_code" => $admin->id,
            'first_name' => 'The',
            'last_name' => 'Client',
            'pid' => 'E-8-124926',
            'email' => 'client@company.com',
            'telephones' => '555-5555, 565-5425',
            'type' => 'E',
            'org_name' => 'The Org Name',
            'status' => 'A',
        ]);

        $response->assertRedirect(route('tenant.client.list'));
        $response->assertSessionHas(['flash_success']);

        $client = $tenant->clients->first();

        $this->assertCount(1, $client->boxes);
    }
}
