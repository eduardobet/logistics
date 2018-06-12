<?php

namespace Tests\Feature\Tenant\Client;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Box;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Client;
use Illuminate\Support\Facades\Event;
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
            'branch_code',
        ]);
    }

    /** @test */
    public function it_successfully_creates_the_client()
    {
        $this->withoutExceptionHandling();

        Event::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);

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
            'branch_code' => 'B-CODE',

            // optional
            'country_id' => 1,
            'department_id' => 1,
            'city_id' => 1,
            'notes' => 'Aditional notes',
            'pay_volume' => 'N',
            'special_rate' => 'N',
            'special_maritime' => 'N',
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

            // optional
            'country_id' => 1,
            'department_id' => 1,
            'city_id' => 1,
            'notes' => 'Aditional notes',
            'pay_volume' => '1',
            'special_rate' => '1',
            'special_maritime' => '1',
        ]);

        $response->assertRedirect(route('tenant.client.list'));
        $response->assertSessionHas(['flash_success']);

        $client = $tenant->clients->first();

        $this->assertCount(1, $client->boxes);

        Event::assertDispatched(\Logistics\Events\Tenant\ClientWasCreatedEvent::class, function ($event) use ($client) {
            return $event->client->id === $client->id;
        });
    }

    /** @test */
    public function the_client_cannot_have_more_than_one_active_box_at_a_time()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $tenant->remoteAddresses()->createMany([
            ['type' => 'A', 'address' => 'In the middle of remote air', 'telephones' => '555-5555', 'status' => 'A', ],
            ['type' => 'M', 'address' => 'In the middle of remote maritimes', 'telephones' => '555-5555', 'status' => 'A', ],
        ]);

        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $box = factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => 1,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);
        $admin->branches()->sync([$branch->id]);

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
            'branch_code' => 'B-CODE',
        ]);

        $client = $tenant->clients->fresh()->last();
        $boxes = $client->boxes;

        $this->assertCount(1, $boxes->where('status', 'A'));
        $this->assertCount(1, $boxes->where('status', 'I'));
    }
}
