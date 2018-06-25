<?php

namespace Tests\Feature\Tenant\Admin\Tenant;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Branch;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TenantEditionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_administrator()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.admin.company.edit', $tenant->domain), []);
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
    }

    /** @test */
    public function admin_can_update_the_company()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        $admin->branches()->sync([$branch->id]);

        $response = $this->actingAs($admin)->get(route('tenant.admin.company.edit', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.company.edit');

        $response = $this->actingAs($admin)->post(route('tenant.admin.company.update', $tenant->domain), [
            '_method' => 'PATCH',
            'name' => 'Company Name Updated',
            'ruc' => 'RUC',
            'dv' => 'DV',
            'telephones' => '555-3454,545-5435',
            'emails' => ' contact@tenant.com, sales@tenant.com ',
            'address' => 'In the middle of nowhere',
            'lang' => 'es',
            'remote_addresses' => [
                ['type' => 'A', 'address' => 'In the middle of remote air', 'telephones' => '555-5555',  'status' => 'A', ],
                ['type' => 'M', 'address' => 'In the middle of remote maritimes', 'telephones' => '555-5555','status' => 'A',  ],
            ],
        ]);

        $this->assertDatabaseHas('tenants', [
            'name' => 'Company Name Updated',
            'domain' => 'middleton-services.test',
            'status' => 'A',
            'ruc' => 'RUC',
            'dv' => 'DV',
            'telephones' => '555-3454,545-5435',
            'emails' => 'contact@tenant.com, sales@tenant.com',
            'address' => 'In the middle of nowhere',
            'lang' => 'es',
        ]);

        
        $this->assertDatabaseHas('remote_addresses', [
            'type' => 'M', 'address' => 'In the middle of remote maritimes', 'telephones' => '555-5556', 'created_by_code' => $admin->id,
            'type' => 'A', 'address' => 'In the middle of remote air', 'telephones' => '555-5555', 'created_by_code' => $admin->id,
        ]);

        $this->assertNotNull(view()->shared('tenant'));
        $this->assertEquals('Company Name Updated', view()->shared('tenant')->name);
            
        $response->assertSessionHas(['flash_success']);
        $response->assertRedirect(route('tenant.admin.company.edit', $tenant->domain));
    }

    /** @test */
    public function admin_cannot_edit_the_domain_nor_the_status_of_the_company()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        $response = $this->actingAs($admin)->post(route('tenant.admin.company.update', $tenant->domain), [
            '_method' => 'PATCH',
            'name' => 'Company Name Updated',
            'ruc' => 'RUC',
            'dv' => 'DV',
            'telephones' => '555-3454,545-5435',
            'emails' => 'email@test.com',
            'address' => 'In the middle of nowhere',
            'lang' => 'es',
            'domain' => 'other-domain.test',
            'current_domain' => $tenant->domain,
            'status' => 'I',
            'remote_addresses' => [
                ['type' => 'A', 'address' => 'In the middle of remote air', 'telephones' => '555-5555', 'status' => 'A', ],
                ['type' => 'M', 'address' => 'In the middle of remote maritimes', 'telephones' => '555-5555', 'status' => 'A', ],
            ],
        ]);

        $response->assertSessionHasErrors(['domain', 'status']);
        $response->assertRedirect(route('tenant.admin.company.edit', $tenant->domain));
        $this->assertEquals('middleton-services.test', $tenant->fresh()->first()->domain);
        $this->assertEquals('Middleton Services S.A.', $tenant->fresh()->first()->name);
    }

    /** @test */
    public function admin_cannot_edit_the_company_with_invalid_inputs()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        $response = $this->actingAs($admin)->post(route('tenant.admin.company.update', $tenant->domain), [
            '_method' => 'PATCH',
            'name' => 'C',
            'telephones' => '555,543',
            'emails' => '555,545',
            'address' => '123',
            'lang' => 'xx',
            'remote_addresses' => [[]],
        ]);

        $response->assertSessionHasErrors([
            'name', 'telephones', 'emails', 'address', 'lang', 'ruc', 'dv',
            'remote_addresses.*.type',
            'remote_addresses.*.address',
            'remote_addresses.*.telephones',
            'remote_addresses.*.status',
        ]);
        $response->assertRedirect(route('tenant.admin.company.edit', $tenant->domain));
        $this->assertEquals('middleton-services.test', ($tenant = $tenant->fresh()->first())->domain);
        $this->assertEquals('Middleton Services S.A.', $tenant->name);
    }

    /** @test */
    public function admin_can_upload_a_company_logo()
    {
        $this->withoutExceptionHandling();

        Storage::fake('public');

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $file = UploadedFile::fake()->image('logo.jpg');

        $response = $this->actingAs($admin)->post(route('tenant.admin.company.update', $tenant->domain), [
            'name' => 'Company Name Updated',
            'ruc' => 'RUC',
            'dv' => 'DV',
            'telephones' => '555-3454,545-5435',
            'emails' => ' contact@tenant.com, sales@tenant.com ',
            'address' => 'In the middle of nowhere',
            'lang' => 'es',
            'logo' => $file,
            'remote_addresses' => [
                ['type' => 'A', 'address' => 'In the middle of remote air', 'telephones' => '555-5555', 'status' => 'A', ],
                ['type' => 'M', 'address' => 'In the middle of remote maritimes', 'telephones' => '555-5555', 'status' => 'A', ],
            ],
            '_method' => 'PATCH',
        ]);

        $tenant = $tenant->fresh()->first();

        $this->assertEquals("tenant/{$tenant->id}/images/logos/{$file->hashName()}", $tenant->logo);
        Storage::disk('public')->assertExists("tenant/{$tenant->id}/images/logos/{$file->hashName()}");
    }

    /** @test */
    public function company_old_logo_is_removed()
    {
        $this->withoutExceptionHandling();

        Storage::fake('public');

        $file = UploadedFile::fake()->image('logo.jpg');
        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        Storage::disk('public')->put(
            "tenant/{$tenant->id}/images/logos/logo.png",
            file_get_contents(storage_path('fixtures/less-than-200px-wide-image.png'))
        );

        $tenant = $tenant->fresh()->first();
        $tenant->logo = "tenant/{$tenant->id}/images/logos/logo.png";
        $tenant->save();

        $response = $this->actingAs($admin)->post(route('tenant.admin.company.update', $tenant->domain), [
            'name' => 'Company Name Updated',
            'ruc' => 'RUC',
            'dv' => 'DV',
            'telephones' => '555-3454,545-5435',
            'emails' => ' contact@tenant.com, sales@tenant.com ',
            'address' => 'In the middle of nowhere',
            'lang' => 'es',
            'logo' => $file,
            'remote_addresses' => [
                ['type' => 'A', 'address' => 'In the middle of remote air', 'telephones' => '555-5555', 'status' => 'A', ],
                ['type' => 'M', 'address' => 'In the middle of remote maritimes', 'telephones' => '555-5555', 'status' => 'A', ],
            ],
            '_method' => 'PATCH',
        ]);

        $tenant = $tenant->fresh()->first();

        Storage::disk('public')->assertMissing("tenant/{$tenant->id}/images/logos/logo.png");
        $this->assertEquals("tenant/{$tenant->id}/images/logos/{$file->hashName()}", $tenant->logo);
        Storage::disk('public')->assertExists("tenant/{$tenant->id}/images/logos/{$file->hashName()}");
    }
}
