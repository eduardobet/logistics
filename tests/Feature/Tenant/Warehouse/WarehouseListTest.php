<?php

namespace Tests\Feature\Tenant\Warehouse;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\User;
use Logistics\DB\Tenant\Client;

class WarehouseListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_like_this_tenant_user()
    {
        // $this->withoutExceptionHandling();
        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.warehouse.list', [$tenant->domain]), []);
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
    }

    /** @test */
    public function it_successfully_lists_the_warehouses()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id,]);

        \Gate::define('show-warehouse', function ($admin) {
            return true;
        });

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, ]);

        $warehouseA = $tenant->warehouses()->create([
            'branch_to' => $branch->id,
            'branch_from' => $branchB->id,
            'client_id' => $client->id,
            'trackings' => '1234',
            'reference' => 'The reference A',
            'qty' => 1,
            'type' => 'A',
        ]);

        $warehouseB = $tenant->warehouses()->create([
            'branch_to' => $branch->id,
            'branch_from' => $branchB->id,
            'client_id' => $client->id,
            'trackings' => '4563',
            'reference' => 'The reference B',
            'qty' => 1,
            'type' => 'A',
        ]);

        $response = $this->actingAs($admin)->get(route('tenant.warehouse.list', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.warehouse.index');

        $this->assertTrue($response->original->getData()['warehouses']->contains($warehouseA));
        $this->assertTrue($response->original->getData()['warehouses']->contains($warehouseB));
    }
}
