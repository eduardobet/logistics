<?php

namespace Tests\Feature\Tenant\Warehouse;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use AlbertCht\InvisibleReCaptcha\Facades\InvisibleReCaptcha;

class MisidentifiedPackageCreationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_does_not_redirect_to_login_if_not_logged_in_like_this_tenant_user()
    {
        // $this->withoutExceptionHandling();
        
        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.misidentified-package.create', $tenant->domain), []);
        $response->assertStatus(200);
    }

    /** @test */
    public function misidentified_package_cannot_be_created_with_invalid_inputs()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        $response = $this->actingAs($admin)->post(route('tenant.misidentified-package.store', $tenant->domain), [
        ], $this->headers());
        $response->assertStatus(500);
    }

    /** @test */
    public function it_successfuly_creates_the_misidentified_packages()
    {
        $this->withoutExceptionHandling();

        InvisibleReCaptcha::shouldReceive('verifyResponse')
            ->once()
            ->andReturn(true);

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch Name', ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id,]);

        $response = $this->actingAs($admin)->get(route('tenant.misidentified-package.create', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.misidentified-package.create');

        $response = $this->actingAs($admin)->post(route('tenant.misidentified-package.store', $tenant->domain), [
            'branch_to' => $branch->id,
            'trackings' => '12345,234434,55645',
            'client_id' => 1,
            'cargo_entry_id' => 1,
            'g-recaptcha-response' => 'g-recaptcha-response',
        ], $this->headers());

        $response->assertStatus(200);

        $this->assertDatabaseHas('misidentified_packages', [
            "tenant_id" => $tenant->id,
            'branch_to' => $branch->id,
            'trackings' => '12345,234434,55645',
            'client_id' => 1,
            'cargo_entry_id' => 1,
        ]);
    }
}
