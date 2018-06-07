<?php

namespace Tests\Feature\Tenant\Admin\Branch;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Branch;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BranchEditionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_administrator()
    {
        // $this->withoutExceptionHandling();
        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.admin.branch.edit', [1]));
        $response->assertRedirect(route('tenant.auth.get.login'));
    }

    /** @test */
    public function branch_can_only_be_edited_by_authenticated_admin()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);

        $response = $this->get(route('tenant.admin.branch.edit', 1));
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.auth.get.login'));

        $this->assertFalse(auth()->check());
    }

    /** @test */
    public function branch_can_only_be_edited_by_admin()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $employee = factory(User::class)->states('employee')->create(['tenant_id' =>$tenant->id, ]);

        $response = $this->actingAs($employee)->get(route('tenant.admin.branch.edit', 1));
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.auth.get.login'));
        $this->assertFalse(auth()->check());
    }

    /** @test */
    public function branch_cannot_be_edited_with_invalid_inputs()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);

        $response = $this->actingAs($admin)->patch(route('tenant.admin.branch.update'), [
            'id' => 1
        ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.admin.branch.edit', [1]));

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
    public function branch_name_must_be_unique()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);
        $branchA = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch Name B']);

        $response = $this->actingAs($admin)->patch(route('tenant.admin.branch.update'), [
            'name' => $branchB->name,
            'address' => 'In the middle of nowhere',
            'emails' => 'contact@branch.test, sales@branch.test',
            'telephones' => '555-5555',
            'code' => 'CODE',
            'status' => 'A',
            'id' => $branchA->id,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.admin.branch.edit', [$branchA->id]));
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function it_successfully_updates_the_branch()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);

        $response = $this->actingAs($admin)->get(route('tenant.admin.branch.edit', [$tenant->id]));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.branch.edit');

        $response = $this->actingAs($admin)->patch(route('tenant.admin.branch.update'), [
            'name' => 'Branch Name Updated',
            'address' => 'In the middle of nowhere',
            'emails' => 'contact@branch.test, sales@branch.test',
            'telephones' => '655-5555',
            'faxes' => '655-5454',
            'code' => 'CODE',
            "lat" => '12234',
            "lng" => '4344',
            "ruc" => '123-23-33',
            "dv" => '04',
            'status' => 'A',
            'id' => $branch->id,
        ]);

        $this->assertDatabaseHas('branches', [
            "tenant_id" => $tenant->id,
            "created_by_code" => null,
            "updated_by_code" => $admin->id,
            'name' => 'Branch Name Updated',
            'address' => 'In the middle of nowhere',
            'emails' => 'contact@branch.test, sales@branch.test',
            'telephones' => '655-5555',
            'faxes' => '655-5454',
            'code' => 'CODE',
            "lat" => '12234',
            "lng" => '4344',
            "ruc" => '123-23-33',
            "dv" => '04',
            'status' => 'A',
        ]);

        $response->assertRedirect(route('tenant.admin.branch.list'));
        $response->assertSessionHas(['flash_success']);
    }
}
