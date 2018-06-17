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

class ClientEditionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_like_this_tenant_user()
    {
        // $this->withoutExceptionHandling();
        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.client.edit', 1), []);
        $response->assertRedirect(route('tenant.auth.get.login'));
    }

    /** @test */
    public function client_cannot_be_updated_with_invalid_inputs()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        $response = $this->actingAs($admin)->patch(route('tenant.client.update', 1), [
            'country_id' => 'xxx',
            'department_id' => 'xxx',
            'city_id' => 'xxx',
            'address' => 'AA',
        ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.client.edit', 1));

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
            'country_id',
            'department_id',
            'city_id',
            'address',
        ]);
    }

    /** @test */
    public function it_successfully_updates_the_client()
    {
        // $this->withoutExceptionHandling();

        Event::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, ]);

        $admin->branches()->sync([$branch->id]);

        $response = $this->actingAs($admin)->get(route('tenant.client.edit', $client->id));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.client.edit');
        $response->assertViewHas(['client']);

        $response = $this->actingAs($admin)->patch(route('tenant.client.update', $client->id), [
            'first_name' => 'The updated',
            'last_name' => 'Client updated',
            'pid' => 'E-8-124925',
            'telephones' => '555-5555',
            'email' => 'client@company.com',
            'type' => 'C',
            'status' => 'I',
            'branch_id' => 1,
            'branch_code' => 'B-CODE',

            // optional
            'country_id' => 1,
            'department_id' => 1,
            'city_id' => 1,
            'address' => 'In the middle of nowhere',
            'notes' => 'Aditional notes updated',
            'pay_volume' => 'Y',
            'special_rate' => 'Y',
            'special_maritime' => 'Y',
        ]);

        $this->assertDatabaseHas('clients', [
            'first_name' => 'The updated',
            'last_name' => 'Client updated',
            'pid' => 'E-8-124925',
            'telephones' => '555-5555',
            'email' => 'client@company.com',
            'type' => 'C',
            'status' => 'I',

            // optional
            'country_id' => 1,
            'department_id' => 1,
            'city_id' => 1,
            'address' => 'In the middle of nowhere',
            'notes' => 'Aditional notes updated',
            'pay_volume' => '1',
            'special_rate' => '1',
            'special_maritime' => '1',
        ]);

        $response->assertRedirect(route('tenant.client.list'));
        $response->assertSessionHas(['flash_success']);

        Event::assertNotDispatched(\Logistics\Events\Tenant\ClientWasCreatedEvent::class);
    }

    /** @test */
    public function client_extra_contacts_can_be_updated()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, ]);

        $admin->branches()->sync([$branch->id]);

        $this->actingAs($admin);

        $extraContact = $client->extraContacts()->create([
            'full_name' => 'Extra Contact',
            'pid' => '1253-587',
            'email' => 'extra-contact@email.test',
            'telephones' => '555-5555',
            'tenant_id' => $tenant->id,
        ]);

        $response = $this->actingAs($admin)->get(route('tenant.client.edit', $client->id));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.client.edit');
        $response->assertViewHas(['client']);

        $response = $this->patch(route('tenant.client.update', $client->id), [
            'first_name' => 'The updated',
            'last_name' => 'Client updated',
            'pid' => 'E-8-124925',
            'telephones' => '555-5555',
            'email' => 'client@company.com',
            'type' => 'C',
            'status' => 'I',
            'branch_id' => 1,
            'branch_code' => 'B-CODE',

            // xtra contacts
            'econtacts' => [
                ['efull_name' => 'Extra Contact update', 'epid' => '1253-587', 'eemail' => 'extra-contact@email.test', 'etelephones' => '555-5556', 'eid' => $extraContact->id, ],
                ['efull_name' => 'New Extra contact', 'epid' => '145', 'eemail' => 'new-extra-contact@email.test', 'etelephones' => '555-5557', 'eid' => null, ]
            ],

        ]);

        $response->assertRedirect(route('tenant.client.list'));
        $response->assertSessionHas(['flash_success']);
         
        tap($client->extraContacts()->where('email', 'extra-contact@email.test')->first(), function ($econtact) use ($admin) {
            $this->assertEquals($admin->id, $econtact->created_by_code);
            $this->assertEquals($admin->id, $econtact->updated_by_code);
        });

        tap($client->fresh()->extraContacts()->where('email', 'new-extra-contact@email.test')->first(), function ($econtact) use ($admin) {
            $this->assertNull($econtact->updated_by_code);
            $this->assertEquals($admin->id, $econtact->created_by_code);
            $this->assertEquals('New Extra contact', $econtact->full_name);
            $this->assertEquals('145', $econtact->pid);
            $this->assertEquals('555-5557', $econtact->telephones);
        });
    }

    /** @test */
    public function client_extra_contacts_can_be_deleted()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, ]);

        $admin->branches()->sync([$branch->id]);

        $this->actingAs($admin);

        $extraContact = $client->extraContacts()->create([
            'full_name' => 'Extra Contact',
            'pid' => '1253-587',
            'email' => 'extra-contact@email.test',
            'telephones' => '555-5555',
            'tenant_id' => $tenant->id,
        ]);

        $response = $this->actingAs($admin)->get(route('tenant.client.edit', $client->id));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.client.edit');
        $response->assertViewHas(['client']);

        $response = $this->delete(route('tenant.client.extra-contact.destroy'), [
            'id' => $extraContact->id,
            'client_id' => $client->id,
        ], $this->headers());

        $response->assertStatus(200);
        $this->assertCount(0, $client->fresh()->extraContacts);
        $response->assertJson([
            'error' => false,
            'msg' => __("Deleted successfully"),
        ]);
    }

    /** @test */
    public function event_is_fired_is_the_email_has_changed()
    {
        $this->withoutExceptionHandling();

        Event::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, ]);

        $admin->branches()->sync([$branch->id]);

        $response = $this->actingAs($admin)->get(route('tenant.client.edit', $client->id));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.client.edit');
        $response->assertViewHas(['client']);

        $response = $this->actingAs($admin)->patch(route('tenant.client.update', $client->id), [
            'first_name' => 'The updated',
            'last_name' => 'Client updated',
            'pid' => 'E-8-124925',
            'telephones' => '555-5555',
            'email' => 'client.update@company.com',
            'type' => 'C',
            'status' => 'I',
            'branch_id' => 1,
            'branch_code' => 'B-CODE',
        ]);

        $this->assertDatabaseHas('clients', [
            'first_name' => 'The updated',
            'last_name' => 'Client updated',
            'pid' => 'E-8-124925',
            'telephones' => '555-5555',
            'type' => 'C',
            'status' => 'I',
            'email' => 'client.update@company.com',
        ]);

        $response->assertRedirect(route('tenant.client.list'));
        $response->assertSessionHas(['flash_success']);

        Event::assertDispatched(\Logistics\Events\Tenant\ClientWasCreatedEvent::class, function ($event) use ($client) {
            return $event->client->id === $client->id;
        });
    }
}
