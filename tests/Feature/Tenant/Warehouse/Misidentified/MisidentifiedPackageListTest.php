<?php

namespace Tests\Feature\Tenant\Warehouse;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MisidentifiedPackageListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_like_this_tenant_user()
    {
        // $this->withoutExceptionHandling();
        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.misidentified-package.index', [$tenant->domain]), []);
        $response->assertStatus(302);
    }

    /** @test */
    public function it_successfully_lists_the_misidentified_packages()
    {
        //$this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id,]);

        \Gate::define('show-misreca', function ($admin) {
            return true;
        });

        $misidentifiedA = $tenant->misidentifiedPackages()->create([
            'branch_to' => $branch->id,
            'trackings' => '12345,234434,55645',
            'client_id' => 1,
            'cargo_entry_id' => 1,
        ]);

        $misidentifiedB = $tenant->misidentifiedPackages()->create([
            'branch_to' => $branch->id,
            'trackings' => '56565454,658887,456565',
            'client_id' => 1,
            'cargo_entry_id' => 1,
        ]);

        $response = $this->actingAs($admin)->get(route('tenant.misidentified-package.index', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.misidentified-package.index');

        $this->assertTrue($response->original->getData()['misidentified_packages']->contains($misidentifiedA));
        $this->assertTrue($response->original->getData()['misidentified_packages']->contains($misidentifiedB));
    }
}
