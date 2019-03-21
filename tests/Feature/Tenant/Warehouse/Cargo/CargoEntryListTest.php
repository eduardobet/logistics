<?php

namespace Tests\Feature\Tenant\Warehouse;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CargoEntryListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_like_this_tenant_user()
    {
        // $this->withoutExceptionHandling();
        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.warehouse.cargo-entry.list', [$tenant->domain]), []);
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
    }

    /** @test */
    public function it_successfully_lists_the_cargo_entries()
    {
        //$this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id,]);

        $this->actingAs($admin);

        \Gate::define('show-reca', function ($user) {
            return true;
        });

        $cargoEntryA = $tenant->cargoEntries()->create([
            'branch_id' => $branch->id,
            'trackings' => '1234',
        ]);

        $cargoEntryB = $tenant->cargoEntries()->create([
            'branch_id' => $branch->id,
            'trackings' => '5687',
        ]);


        $response = $this->get(route('tenant.warehouse.cargo-entry.list', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.warehouse.cargo-entry.list');

        $this->assertTrue($response->original->getData()['cargo_entries']->contains($cargoEntryA));
        $this->assertTrue($response->original->getData()['cargo_entries']->contains($cargoEntryB));
    }
}
