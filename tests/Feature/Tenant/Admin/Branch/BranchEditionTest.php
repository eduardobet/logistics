<?php

namespace Tests\Feature\Tenant\Admin\Branch;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Branch;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

        $response = $this->get(route('tenant.admin.branch.edit', [$tenant->domain, 1]));
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
    }

    /** @test */
    public function branch_can_only_be_edited_by_authenticated_admin()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);

        $response = $this->get(route('tenant.admin.branch.edit', [$tenant->domain, 1]));
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));

        $this->assertFalse(auth()->check());
    }

    /** @test */
    public function branch_can_only_be_edited_by_admin()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $employee = factory(User::class)->states('employee')->create(['tenant_id' =>$tenant->id, ]);

        $response = $this->actingAs($employee)->get(route('tenant.admin.branch.edit', [$tenant->domain, 1]));
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
        $this->assertFalse(auth()->check());
    }

    /** @test */
    public function branch_cannot_be_edited_with_invalid_inputs()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);

        $response = $this->actingAs($admin)->patch(route('tenant.admin.branch.update', $tenant->domain), [
            'id' => 1,
            'real_price' => 'XX',
            'vol_price' => 'XX',
            'dhl_price' => 'XX',
            'maritime_price' => 'XX',
            'first_lbs_price' => 'XX',
            'logo' => 'invalid',
        ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.admin.branch.edit', [$tenant->domain, 1]));

        $response->assertSessionHasErrors([
            'name',
            'code',
            'initial',
            'address',
            'emails',
            'telephones',
            'status',
            'real_price',
            'vol_price',
            'dhl_price',
            'maritime_price',
            'first_lbs_price',
            'color',
            'logo',
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

        $response = $this->actingAs($admin)->patch(route('tenant.admin.branch.update', $tenant->domain), [
            'name' => $branchB->name,
            'address' => 'In the middle of nowhere',
            'emails' => 'contact@branch.test, sales@branch.test',
            'telephones' => '555-5555',
            'code' => 'CODE',
            'status' => 'A',
            'id' => $branchA->id,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.admin.branch.edit', [$tenant->domain, $branchA->id]));
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function it_successfully_updates_the_branch()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);

        $admin->branches()->sync([$branch->id]);
        
        $response = $this->actingAs($admin)->get(route('tenant.admin.branch.edit', [$tenant->domain, $tenant->id]));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.branch.edit');

        $response = $this->actingAs($admin)->patch(route('tenant.admin.branch.update', $tenant->domain), [
            'name' => 'Branch Name Updated',
            'address' => 'In the middle of nowhere',
            'emails' => 'contact@branch.test, sales@branch.test',
            'telephones' => '655-5555',
            'faxes' => '655-5454',
            'code' => 'CODE',
            'initial' => 'INIT',
            "lat" => '12234',
            "lng" => '4344',
            "ruc" => '123-23-33',
            "dv" => '04',
            'status' => 'A',
            'direct_comission' => '1',
            'should_invoice' => '1',
            'id' => $branch->id,

            'real_price' => 2.50,
            'vol_price' => 1.75,
            'dhl_price' => 2.25,
            'maritime_price' => 250,
            'first_lbs_price' => 10,
            'color' => 'blue',
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
            'initial' => 'INIT',
            "lat" => '12234',
            "lng" => '4344',
            "ruc" => '123-23-33',
            "dv" => '04',
            'status' => 'A',
            'direct_comission' => 1,
            'should_invoice' => 1,

            'real_price' => 2.50,
            'vol_price' => 1.75,
            'dhl_price' => 2.25,
            'maritime_price' => 250,
            'first_lbs_price' => 10,
            'color' => 'blue',
        ]);

        $response->assertRedirect(route('tenant.admin.branch.list', $tenant->domain));
        $response->assertSessionHas(['flash_success']);
    }

    /** @test */
    public function branch_logo_can_be_edited_and_the_old_one_is_removed()
    {
        $this->withoutExceptionHandling();

        Storage::fake('public');

        $tenant = factory(TenantModel::class)->create();
        $file = UploadedFile::fake()->image('logo.jpg');
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);

        Storage::disk('public')->put(
            "tenant/{$tenant->id}/images/logos/logo.png",
            file_get_contents(storage_path('fixtures/less-than-200px-wide-image.png'))
        );

        $branch = $branch->fresh()->first();
        $branch->logo = "tenant/{$tenant->id}/images/logos/logo.png";
        $branch->save();

        $response = $this->actingAs($admin)->patch(route('tenant.admin.branch.update', $tenant->domain), [
            'name' => 'Branch Name Updated',
            'address' => 'In the middle of nowhere',
            'emails' => 'contact@branch.test, sales@branch.test',
            'telephones' => '655-5555',
            'faxes' => '655-5454',
            'code' => 'CODE',
            'initial' => 'INIT',
            "lat" => '12234',
            "lng" => '4344',
            "ruc" => '123-23-33',
            "dv" => '04',
            'status' => 'A',
            'direct_comission' => '1',
            'should_invoice' => '1',
            'id' => $branch->id,

            'real_price' => 2.50,
            'vol_price' => 1.75,
            'dhl_price' => 2.25,
            'maritime_price' => 250,
            'color' => 'blue',
            'logo' => $file,
        ]);

        $branch = $branch->fresh()->first();

        Storage::disk('public')->assertMissing("tenant/{$tenant->id}/images/logos/logo.png");
        $this->assertEquals("tenant/{$tenant->id}/images/logos/{$file->hashName()}", $branch->logo);
        Storage::disk('public')->assertExists("tenant/{$tenant->id}/images/logos/{$file->hashName()}");
    }
}
