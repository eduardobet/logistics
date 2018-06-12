<?php

namespace Tests\Feature\Tenant\Admin\Branch;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Branch;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BranchCreationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_administrator()
    {
        // $this->withoutExceptionHandling();
        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.admin.branch.create'), []);
        $response->assertRedirect(route('tenant.auth.get.login'));
    }

    /** @test */
    public function branch_can_only_be_created_by_authenticated_admin()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);

        $response = $this->get(route('tenant.admin.branch.create'), []);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.auth.get.login'));

        $this->assertFalse(auth()->check());
    }

    /** @test */
    public function branch_can_only_be_created_by_admin()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $employee = factory(User::class)->states('employee')->create(['tenant_id' =>$tenant->id, ]);

        $response = $this->actingAs($employee)->get(route('tenant.admin.branch.create'), []);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.auth.get.login'));
        $this->assertFalse(auth()->check());
    }

    /** @test */
    public function branch_cannot_be_created_with_invalid_inputs()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);

        $response = $this->actingAs($admin)->post(route('tenant.admin.branch.store'), []);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.admin.branch.create'));

        $response->assertSessionHasErrors([
            'name',
            'code',
            'address',
            'emails',
            'telephones',
            'status',
        ]);
    }

    /** @test */
    public function branche_name_must_be_unique()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($admin)->post(route('tenant.admin.branch.store'), [
            'name' => $branch->name,
            'address' => 'In the middle of nowhere',
            'emails' => 'contact@branch.test, sales@branch.test',
            'telephones' => '555-5555',
            'code' => 'CODE',
            'status' => 'A',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.admin.branch.create'));
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function it_successfully_creates_the_branch()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $admin->branches()->sync([$branch->id]);

        $response = $this->actingAs($admin)->get(route('tenant.admin.branch.create'));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.branch.create');

        $response = $this->actingAs($admin)->post(route('tenant.admin.branch.store'), [
            'name' => 'Branch Name one',
            'address' => 'In the middle of nowhere',
            'emails' => 'contact@branch.test, sales@branch.test',
            'telephones' => '555-5555',
            'faxes' => '555-5454',
            'code' => 'CODE',
            "lat" => '12234',
            "lng" => '4344',
            "ruc" => 'RUC',
            "dv" => 'DV',
            'status' => 'A',
        ]);

        $this->assertDatabaseHas('branches', [
            "tenant_id" => $tenant->id,
            "created_by_code" => $admin->id,
            "name" => "Branch Name one",
            "code" => "CODE",
            "address" => "In the middle of nowhere",
            "telephones" => "555-5555",
            "emails" => "contact@branch.test, sales@branch.test",
            "faxes" => '555-5454',
            "lat" => '12234',
            "lng" => '4344',
            "ruc" => 'RUC',
            "dv" => 'DV',
            "status" => "A",
        ]);

        $response->assertRedirect(route('tenant.admin.branch.list'));
        $response->assertSessionHas(['flash_success']);
    }
}
