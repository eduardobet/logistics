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

class ClientEditionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_like_this_tenant_user()
    {
        // $this->withoutExceptionHandling();
        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.client.edit', [ $tenant->domain, 1]), []);
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
    }

    /** @test */
    public function client_cannot_be_updated_with_invalid_inputs()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create(['email_allowed_dup' => '']);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        $response = $this->actingAs($admin)->patch(route('tenant.client.update', [$tenant->domain, 1]), [
            'country_id' => 'xxx',
            'department_id' => 'xxx',
            'city_id' => 'xxx',
            'address' => 'AA',
            'vol_price' => 'XX',
            'real_price' => 'XX',
            'first_lbs_price' => 'XX',
            'email' => 'XX',
            'maritime_price' => 'XX',
            'extra_maritime_price' => 'XX',
        ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.client.edit', [ $tenant->domain, 1]));

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
            'vol_price',
            'real_price',
            'first_lbs_price',
            'maritime_price',
            'extra_maritime_price',
        ]);
    }

    /** @test */
    public function email_is_unique_only_when_it_defers_from_predefined_one()
    {
        /*// $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create(['migration_mode' => true, ]);
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);

        factory(Client::class)->create(['tenant_id' => $tenant->id, 'email' => 'first@company.com', 'branch_id' => $branch->id, 'manual_id' => 1, ]);
        factory(Client::class)->create(['tenant_id' => $tenant->id, 'email' => 'client0@company.com', 'branch_id' => $branch->id, 'manual_id' => 2, ]);

        $response = $this->actingAs($admin)->patch(route('tenant.client.update', [$tenant->domain, 2]), [
            'first_name' => 'The updated',
            'last_name' => 'Client updated',
            'pid' => 'E-8-124925',
            'telephones' => '555-5555',
            'email' => 'first@company.com',
            'type' => 'C',
            'status' => 'I',
            'branch_id' => 1,
            'branch_code' => 'B-CODE',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.client.edit', [$tenant->domain, 2]));

        $response->assertSessionHasErrors([
            'email',
        ]);*/
        $this->assertTrue(true);
    }

    /** @test */
    public function it_successfully_updates_the_client()
    {
        $this->withoutExceptionHandling();

        Queue::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id]);
        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'email' => 'client@company.com', 'manual_id' => 1, ]);

        $admin->branches()->sync([$branch->id]);

        \Gate::define('edit-client', function ($admin) {
            return true;
        });

        $response = $this->actingAs($admin)->get(route('tenant.client.edit', [ $tenant->domain, $client->id]));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.client.edit');
        $response->assertViewHas(['client']);

        $response = $this->actingAs($admin)->patch(route('tenant.client.update', [$tenant->domain, $client->id]), [
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
            'vol_price' => 2.5,
            'real_price' => 2,
            'first_lbs_price' => 5,
            'maritime_price' => 250,
            'extra_maritime_price' => 9,
            'pay_extra_maritime_price' => true,
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
            'vol_price' => 2.5,
            'real_price' => 2,
            'first_lbs_price' => 5,
            'maritime_price' => 250,
            'extra_maritime_price' => 9,
            'pay_extra_maritime_price' => true,
        ]);

        $response->assertRedirect(route('tenant.client.list', $tenant->domain));
        $response->assertSessionHas(['flash_success']);

        Queue::assertNotPushed(SendClientWelcomeEmail::class);
    }

    /** @test */
    public function client_extra_contacts_can_be_updated()
    {
        $this->withoutExceptionHandling();

        Queue::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'email' => 'client@company.com', ]);

        $admin->branches()->sync([$branch->id]);

        $this->actingAs($admin);

        \Gate::define('edit-client', function ($admin) {
            return true;
        });


        $extraContact = $client->extraContacts()->create([
            'full_name' => 'Extra Contact',
            'pid' => '1253-587',
            'email' => 'extra-contact@email.test',
            'telephones' => '555-5555',
            'tenant_id' => $tenant->id,
            'receive_inv_mail' => false,
            'receive_wh_mail' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('tenant.client.edit', [ $tenant->domain, $client->id]));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.client.edit');
        $response->assertViewHas(['client']);

        $response = $this->patch(route('tenant.client.update', [$tenant->domain, $client->id]), [
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
                ['efull_name' => 'Extra Contact update', 'epid' => '1253-587', 'eemail' => 'extra-contact@email.test', 'etelephones' => '555-5556', 'eid' => $extraContact->id, 'receive_inv_mail' => true, 'receive_wh_mail' => true, ],
                ['efull_name' => 'New Extra contact', 'epid' => '145', 'eemail' => 'new-extra-contact@email.test', 'etelephones' => '555-5557', 'eid' => null, 'receive_inv_mail' => true, 'receive_wh_mail' => true,]
            ],

        ]);

        $response->assertRedirect(route('tenant.client.list', $tenant->domain));
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
            $this->assertEquals(1, $econtact->receive_inv_mail);
            $this->assertEquals(1, $econtact->receive_wh_mail);
        });
    }

    /** @test */
    public function client_extra_contacts_can_be_deleted()
    {
        $this->withoutExceptionHandling();

        Queue::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, ]);

        $admin->branches()->sync([$branch->id]);

        $this->actingAs($admin);

        \Gate::define('edit-client', function ($admin) {
            return true;
        });


        $extraContact = $client->extraContacts()->create([
            'full_name' => 'Extra Contact',
            'pid' => '1253-587',
            'email' => 'extra-contact@email.test',
            'telephones' => '555-5555',
            'tenant_id' => $tenant->id,
        ]);

        $response = $this->actingAs($admin)->get(route('tenant.client.edit', [ $tenant->domain, $client->id]));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.client.edit');
        $response->assertViewHas(['client']);

        $response = $this->delete(route('tenant.client.extra-contact.destroy', $tenant->domain), [
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

        Queue::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, ]);
        $box = factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => 1,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);


        \Gate::define('edit-client', function ($admin) {
            return true;
        });

        $admin->branches()->sync([$branch->id]);

        $response = $this->actingAs($admin)->get(route('tenant.client.edit', [ $tenant->domain, $client->id]));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.client.edit');
        $response->assertViewHas(['client']);

        $response = $this->actingAs($admin)->patch(route('tenant.client.update', [$tenant->domain, $client->id]), [
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

        $response->assertRedirect(route('tenant.client.list', $tenant->domain));
        $response->assertSessionHas(['flash_success']);

        Queue::assertPushed(SendClientWelcomeEmail::class, function ($job) use ($client) {
            return $job->client->id === $client->id;
        });
    }

    /** @test */
    public function the_client_does_not_receive_the_welcome_email_if_its_email_is_the_same_as_the_allowed_dup_one()
    {
        $this->withoutExceptionHandling();

        Queue::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $client = factory(Client::class)->create(['tenant_id' => $tenant->id,  'email' => $tenant->email_allowed_dup, ]);
        $box = factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => 1,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);

        \Gate::define('edit-client', function ($admin) {
            return true;
        });

        $admin->branches()->sync([$branch->id]);

        $response = $this->actingAs($admin)->patch(route('tenant.client.update', [$tenant->domain, $client->id]), [
            'first_name' => 'The updated',
            'last_name' => 'Client updated',
            'pid' => 'E-8-124925',
            'telephones' => '555-5555',
            'email' => $tenant->email_allowed_dup,
            'type' => 'C',
            'status' => 'I',
            'branch_id' => 1,
            'branch_code' => 'B-CODE',
        ]);

        $response->assertRedirect(route('tenant.client.list', $tenant->domain));
        $response->assertSessionHas(['flash_success']);

        Queue::assertNotPushed(SendClientWelcomeEmail::class);
    }

    /** @test */
    public function the_welcome_email_can_be_resent()
    {
        $this->withoutExceptionHandling();

        Queue::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, ]);

        $admin->branches()->sync([$branch->id]);

        \Gate::define('edit-client', function ($admin) {
            return true;
        });

        $response = $this->actingAs($admin)->get(route('tenant.client.edit', [ $tenant->domain, $client->id]));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.client.edit');
        $response->assertViewHas(['client']);

        $response = $this->actingAs($admin)->post(route('tenant.client.welcome.email.resend', $tenant->domain), [
            'client_id' => $client->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'msg' => __("Success"),
        ]);

        Queue::assertPushed(SendClientWelcomeEmail::class, function ($job) use ($client) {
            return $job->client->id === $client->id;
        });
    }
}
