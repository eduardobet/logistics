<?php

namespace Tests\Feature\Tenant\Admin\Branch;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Branch;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BranchListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_administrator()
    {
        // $this->withoutExceptionHandling();
        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.admin.branch.list'));
        $response->assertRedirect(route('tenant.auth.get.login'));
    }

    /** @test */
    public function branch_can_only_be_listed_by_authenticated_admin()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);

        $response = $this->get(route('tenant.admin.branch.list'));
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.auth.get.login'));

        $this->assertFalse(auth()->check());
    }

    /** @test */
    public function branch_can_only_be_listed_by_admin()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $employee = factory(User::class)->states('employee')->create(['tenant_id' =>$tenant->id, ]);

        $response = $this->actingAs($employee)->get(route('tenant.admin.branch.list'));
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.auth.get.login'));
        $this->assertFalse(auth()->check());
    }

    /** @test */
    public function it_successfully_lists_the_branches()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branchA = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch Name B']);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);

        $response = $this->actingAs($admin)->get(route('tenant.admin.branch.list'));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.branch.index');

        $this->assertTrue($response->original->getData()['branches']->contains($branchA));
        $this->assertTrue($response->original->getData()['branches']->contains($branchB));
    }

    /** @test */
    public function admin_can_search_by_branch_name_or_id()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branchA = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch Name B']);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);

        $response = $this->actingAs($admin)->get(route('tenant.admin.branch.list', [
            'filter' => 2
        ]));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.branch.index');

        $this->assertFalse($response->original->getData()['branches']->contains($branchA));
        $this->assertTrue($response->original->getData()['branches']->contains($branchB));
    }
}
