<?php

namespace Tests\Feature\Tenant\Client;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Box;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Client;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Logistics\Jobs\Tenant\SendClientWelcomeEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClientCreationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_like_this_tenant_user()
    {
        // $this->withoutExceptionHandling();
        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.client.create', $tenant->domain), []);
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
    }

    /** @test */
    public function client_cannot_be_created_with_invalid_inputs()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create(['email_allowed_dup' => '', ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        $response = $this->actingAs($admin)->post(route('tenant.client.store', $tenant->domain), [
            'country_id' => 'xxx',
            'department_id' => 'xxx',
            'city_id' => 'xxx',
            'address' => 'AA',
            'vol_price' => 'XX',
            'real_price' => 'XX',
            'email' => 'XX',
        ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.client.create', $tenant->domain));

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
        ]);
    }

    /** @test */
    public function it_validates_the_id_when_manually_provided()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create(['migration_mode' => true, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        $response = $this->actingAs($admin)->post(route('tenant.client.store', $tenant->domain), [
            'manual_id' => 'xxx',
        ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.client.create', $tenant->domain));

        $response->assertSessionHasErrors([
            'manual_id',
        ]);
    }

    /** @test */
    public function manual_id_shoul_be_unique_by_branch()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create(['migration_mode' => true, ]);
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);

        factory(Client::class)->create(['tenant_id' => $tenant->id, 'email' => 'client@company.com', 'branch_id' => $branch->id, 'manual_id' => 1, ]);


        $response = $this->actingAs($admin)->post(route('tenant.client.store', $tenant->domain), [
            'first_name' => 'The',
            'last_name' => 'Client',
            'pid' => 'E-8-124926',
            'email' => 'client33@company.com',
            'telephones' => '555-5555, 565-5425',
            'type' => 'E',
            'org_name' => 'The Org Name',
            'status' => 'A',
            'branch_id' => 1,
            'branch_code' => 'B-CODE',
            'branch_initial' => 'B-INITIAL',
            'manual_id' => 1,
        ]);
        
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.client.create', $tenant->domain));

        $response->assertSessionHasErrors([
            'manual_id',
        ]);
    }

    /** @test */
    public function email_is_unique_only_when_it_defers_from_predefined_one()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create(['migration_mode' => true, ]);
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);

        factory(Client::class)->create(['tenant_id' => $tenant->id, 'email' => 'client0@company.com', 'branch_id' => $branch->id, 'manual_id' => 1, ]);

        $response = $this->actingAs($admin)->post(route('tenant.client.store', $tenant->domain), [
            'first_name' => 'The',
            'last_name' => 'Client',
            'pid' => 'E-8-124926',
            'email' => 'client0@company.com',
            'telephones' => '555-5555, 565-5425',
            'type' => 'E',
            'org_name' => 'The Org Name',
            'status' => 'A',
            'branch_id' => 1,
            'branch_code' => 'B-CODE',
            'branch_initial' => 'B-INITIAL',
            'manual_id' => 10,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.client.create', $tenant->domain));

        $response->assertSessionHasErrors([
            'email',
        ]);
    }

    /** @test */
    public function it_successfully_creates_the_client()
    {
        $this->withoutExceptionHandling();

        Queue::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);

        $tenant->remoteAddresses()->createMany([
            ['type' => 'A', 'address' => '8450 NW 70 TH ST MIAMI, FLORIDA 33166-2687', 'telephones' => '+1(786)3252841', 'status' => 'A', ],
            ['type' => 'M', 'address' => '8454 NW 70 TH ST MIAMI, FLORIDA 33166', 'telephones' => '+1(786)3252841', 'status' => 'A', ],
        ]);

        \Gate::define('create-client', function ($admin) {
            return true;
        });

        $response = $this->actingAs($admin)->get(route('tenant.client.create', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.client.create');

        $response = $this->actingAs($admin)->post(route('tenant.client.store', $tenant->domain), [
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
            'branch_initial' => 'B-INITIAL',

            // optional
            'country_id' => 1,
            'department_id' => 1,
            'city_id' => 1,
            'address' => 'In the middle of nowhere',
            'notes' => 'Aditional notes',
            'pay_volume' => 'Y',
            'special_rate' => 'Y',
            'special_maritime' => 'Y',
            'vol_price' => 2.5,
            'real_price' => 2,
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
            'branch_id' => $branch->id,

            // optional
            'country_id' => 1,
            'department_id' => 1,
            'city_id' => 1,
            'address' => 'In the middle of nowhere',
            'notes' => 'Aditional notes',
            'pay_volume' => '1',
            'special_rate' => '1',
            'special_maritime' => '1',
            'vol_price' => 2.5,
            'real_price' => 2,

            'manual_id' => 1,
        ]);

        $response->assertRedirect(route('tenant.client.list', $tenant->domain));
        $response->assertSessionHas(['flash_success']);

        $client = $tenant->clients->fresh()->first();

        tap($client->boxes->first(), function ($box) use ($branch) {
            $this->assertEquals($box->branch_id, $branch->id);
            $this->assertEquals($box->branch_code, 'B-CODE');
            $this->assertEquals($box->branch_initial, 'B-INITIAL');
        });

        Queue::assertPushed(SendClientWelcomeEmail::class, function ($job) use ($client) {
            return $job->client->id === $client->id;
        });
    }

    /** @test */
    public function it_successfully_increment_manual_id_when_not_in_migration_mode()
    {
        // $this->withoutExceptionHandling();

        Queue::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);

        $tenant->remoteAddresses()->createMany([
            ['type' => 'A', 'address' => '8450 NW 70 TH ST MIAMI, FLORIDA 33166-2687', 'telephones' => '+1(786)3252841', 'status' => 'A', ],
            ['type' => 'M', 'address' => '8454 NW 70 TH ST MIAMI, FLORIDA 33166', 'telephones' => '+1(786)3252841', 'status' => 'A', ],
        ]);

        factory(Client::class)->create(['tenant_id' => $tenant->id, 'email' => 'client0@company.com', 'manual_id' => 15, ]);

        \Gate::define('create-client', function ($admin) {
            return true;
        });

        $response = $this->actingAs($admin)->get(route('tenant.client.create', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.client.create');

        $response = $this->actingAs($admin)->post(route('tenant.client.store', $tenant->domain), [
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
            'branch_initial' => 'B-INITIAL',

            // optional
            'country_id' => 1,
            'department_id' => 1,
            'city_id' => 1,
            'address' => 'In the middle of nowhere',
            'notes' => 'Aditional notes',
            'pay_volume' => 'Y',
            'special_rate' => 'Y',
            'special_maritime' => 'Y',
            'vol_price' => 2.5,
            'real_price' => 2,
        ]);

        $lastClient = $tenant->clients->fresh()->last();

        $this->assertEquals(16, $lastClient->manual_id);

        Queue::assertPushed(SendClientWelcomeEmail::class, function ($job) use ($lastClient) {
            return $job->client->id === $lastClient->id;
        });
    }

    /** @test */
    public function it_successfully_creates_the_client_without_autoincrementing()
    {
        $this->withoutExceptionHandling();

        Queue::fake();

        $tenant = factory(TenantModel::class)->create(['migration_mode' => true, ]);
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);

        $tenant->remoteAddresses()->createMany([
            ['type' => 'A', 'address' => '8450 NW 70 TH ST MIAMI, FLORIDA 33166-2687', 'telephones' => '+1(786)3252841', 'status' => 'A', ],
            ['type' => 'M', 'address' => '8454 NW 70 TH ST MIAMI, FLORIDA 33166', 'telephones' => '+1(786)3252841', 'status' => 'A', ],
        ]);

        $clientA = factory(Client::class)->create(['tenant_id' => $tenant->id, 'email' => 'client@company.com', ]);

        \Gate::define('create-client', function ($admin) {
            return true;
        });

        $response = $this->actingAs($admin)->get(route('tenant.client.create', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.client.create');

        $response = $this->actingAs($admin)->post(route('tenant.client.store', $tenant->domain), [
            'first_name' => 'Other',
            'last_name' => 'Client',
            'pid' => 'PID',
            'email' => 'client.2@company.com',
            'telephones' => '555-5555, 565-5425',
            'type' => 'E',
            'org_name' => 'The Org Name',
            'status' => 'A',
            'branch_id' => 1,
            'branch_code' => 'B-CODE',
            'branch_initial' => 'B-INITIAL',

            'manual_id' => 5,
        ]);

        $response->assertRedirect(route('tenant.client.list', $tenant->domain));
        $response->assertSessionHas(['flash_success']);

        $client = $tenant->clients->fresh()->where('manual_id', 5)->first();

        tap($client->boxes->first(), function ($box) use ($branch, $client) {
            $this->assertEquals($box->branch_id, $branch->id);
            $this->assertEquals($box->branch_code, 'B-CODE');
            $this->assertEquals($box->branch_initial, 'B-INITIAL');
            $this->assertEquals($box->client_id, $client->id);
            $this->assertEquals(5, $client->manual_id);
        });

        Queue::assertPushed(SendClientWelcomeEmail::class, function ($job) use ($client) {
            return $job->client->id === $client->id;
        });
    }

    /** @test */
    public function client_extra_contacts_can_be_created()
    {
        $this->withoutExceptionHandling();
        
        Queue::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);

        $tenant->remoteAddresses()->createMany([
            ['type' => 'A', 'address' => '8450 NW 70 TH ST MIAMI, FLORIDA 33166-2687', 'telephones' => '+1(786)3252841', 'status' => 'A', ],
            ['type' => 'M', 'address' => '8454 NW 70 TH ST MIAMI, FLORIDA 33166', 'telephones' => '+1(786)3252841', 'status' => 'A', ],
        ]);


        $this->actingAs($admin);

        $response = $this->post(route('tenant.client.store', $tenant->domain), [
            'first_name' => 'The',
            'last_name' => 'Client',
            'pid' => 'E-8-124925',
            'telephones' => '555-5555',
            'email' => 'client.xx@company.com',
            'type' => 'C',
            'status' => 'I',
            'branch_id' => 1,
            'branch_code' => 'B-CODE',

            // xtra contacts
            'econtacts' => [
                ['efull_name' => 'Extra contact', 'epid' => '145', 'eemail' => 'extra-contact@email.test', 'etelephones' => '555-5557', 'eid' => null, ]
            ],

        ]);

        $response->assertRedirect(route('tenant.client.list', $tenant->domain));
        $response->assertSessionHas(['flash_success']);

        $client = $tenant->clients->first();

        $this->assertCount(1, $client->extraContacts);
         
        tap($client->extraContacts->first(), function ($econtact) use ($admin) {
            $this->assertNull($econtact->updated_by_code);
            $this->assertEquals('Extra contact', $econtact->full_name);
            $this->assertEquals('145', $econtact->pid);
            $this->assertEquals('555-5557', $econtact->telephones);
        });
    }

    /** @test */
    public function the_client_cannot_have_more_than_one_active_box_at_a_time()
    {
        $this->withoutExceptionHandling();
        Queue::fake();


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

        \Gate::define('create-client', function ($admin) {
            return true;
        });


        $response = $this->actingAs($admin)->get(route('tenant.client.create', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.client.create');

        $response = $this->actingAs($admin)->post(route('tenant.client.store', $tenant->domain), [
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

    /** @test */
    public function the_client_does_not_receive_the_welcome_email_if_its_email_is_the_same_as_the_allowed_dup_one()
    {
        $this->withoutExceptionHandling();

        Queue::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);

        $tenant->remoteAddresses()->createMany([
            ['type' => 'A', 'address' => '8450 NW 70 TH ST MIAMI, FLORIDA 33166-2687', 'telephones' => '+1(786)3252841', 'status' => 'A', ],
            ['type' => 'M', 'address' => '8454 NW 70 TH ST MIAMI, FLORIDA 33166', 'telephones' => '+1(786)3252841', 'status' => 'A', ],
        ]);

        \Gate::define('create-client', function ($admin) {
            return true;
        });

        $response = $this->actingAs($admin)->get(route('tenant.client.create', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.client.create');

        $response = $this->actingAs($admin)->post(route('tenant.client.store', $tenant->domain), [
            'first_name' => 'The',
            'last_name' => 'Client',
            'pid' => 'E-8-124926',
            'email' => 'admin@sealcargotrack.com',
            'telephones' => '555-5555, 565-5425',
            'type' => 'E',
            'org_name' => 'The Org Name',
            'status' => 'A',
            'branch_id' => 1,
            'branch_code' => 'B-CODE',
            'branch_initial' => 'B-INITIAL',
        ]);

        $response->assertRedirect(route('tenant.client.list', $tenant->domain));
        $response->assertSessionHas(['flash_success']);

        $client = $tenant->clients->fresh()->first();

        Queue::assertNotPushed(SendClientWelcomeEmail::class);
    }
}
