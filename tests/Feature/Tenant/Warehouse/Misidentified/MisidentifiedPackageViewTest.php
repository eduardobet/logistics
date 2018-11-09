<?php

namespace Tests\Feature\Tenant\Warehouse;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Box;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Client;
use Logistics\DB\Tenant\Mailer;
use Logistics\DB\Tenant\Invoice;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MisidentifiedPackageViewTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_successfully_shows_the_misidentified_package()
    {
        /// $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id,]);

        $misidentified = $tenant->misidentifiedPackages()->create([
            'branch_to' => $branch->id,
            'trackings' => '12345,234434,55645',
            'client_id' => 1,
            'cargo_entry_id' => 1,
        ]);

        $response = $this->actingAs($admin)->get(route('tenant.misidentified-package.show', [$tenant->domain, $misidentified->id]));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.misidentified-package.show');

        $response->assertViewHas(['misidentified_package', ]);
    }
}
