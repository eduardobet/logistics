<?php

namespace Tests\Feature\Tenant\Admin\Employee;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Client;
use Illuminate\Support\Facades\Queue;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserFromClientCreationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_administrator()
    {
        // $this->withoutExceptionHandling();
        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.client.list', $tenant->domain), []);
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
    }

    /** @test */
    public function user_client_cannot_be_created_with_invalid_inputs()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);

        $response = $this->actingAs($admin)->post(route('tenant.admin.user-client.store', $tenant->domain), [
        ]);
        
        $response->assertStatus(500);

        $this->assertArraySubset([
            'error' => true,
        ], $response->json());
    }

    /** @test */
    public function it_successfully_creates_the_user_client()
    {
        $this->withoutExceptionHandling();

        Queue::fake();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $admin->branches()->sync([$branch->id]);
        $client = factory(Client::class)->create();

        $response = $this->actingAs($admin)->post(route('tenant.admin.user-client.store', $tenant->domain), [
            'first_name' => $client->first_name,
            'last_name' => $client->last_name,
            'email' => $client->email,
            'branch_id' => $client->branch_id,
            'telephones' => $client->telephones,
            'password' => 'secret123',
            'client_id' => $client->id,
        ]);

        $response->assertJson([
            'error' => false,
            'msg' => __("Success"),
        ]);
 
        tap($tenant->employees->where('type', 'C')->fresh()->first(), function ($user) use ($admin, $client) {
            $this->assertEquals($client->id, $user->client_id);
            $this->assertEquals($client->first_name, $user->first_name);
            $this->assertEquals($client->last_name, $user->last_name);
            $this->assertEquals($client->email, $user->email);
            $this->assertEquals('C', $user->type);
            $this->assertEquals('A', $user->status);
            $this->assertNotNull($user->password);
            $this->assertEquals($user->is_main_admin, false);
            $this->assertEquals($client->full_name, $user->full_name);
            $this->assertEquals($client->telephones, $user->telephones);
            $this->assertNull($user->updated_by_code);
            $this->assertEquals($user->created_by_code, $admin->id);

            $this->assertCount(1, $user->branches);
        });
    }
}
