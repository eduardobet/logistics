<?php

namespace Tests\Feature\Tenant\Warehouse;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CargoEntryCreationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_like_this_tenant_user()
    {
        // $this->withoutExceptionHandling();
        
        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.warehouse.cargo-entry.create', $tenant->domain), []);
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
    }

    /** @test */
    public function cargo_entry_cannot_be_created_with_invalid_inputs()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        $response = $this->actingAs($admin)->post(route('tenant.warehouse.cargo-entry.store', $tenant->domain), [
        ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.warehouse.cargo-entry.create', $tenant->domain));

        $response->assertSessionHasErrors([
            'branch_id', 'trackings',
        ]);
    }

    /** @test */
    public function it_successfuly_creates_the_cargo_entry()
    {
        $this->withoutExceptionHandling();

        // Notification::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch Name', ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id,]);

        $response = $this->actingAs($admin)->get(route('tenant.warehouse.cargo-entry.create', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.warehouse.cargo-entry.create');

        $response = $this->actingAs($admin)->post(route('tenant.warehouse.cargo-entry.store', $tenant->domain), [
            'branch_id' => $branch->id,
            'trackings' => '12345,234434,55645',
        ]);
        
        $this->assertDatabaseHas('cargo_entries', [
            "tenant_id" => $tenant->id,
            "created_by_code" => $admin->id,
            'branch_id' => $branch->id,
            'trackings' => '12345,234434,55645',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.warehouse.cargo-entry.show', [$tenant->domain, 1]));
    }
}
