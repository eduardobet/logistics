<?php

namespace Tests\Feature\Tenant\Admin\Tenant;

use Tests\TestCase;
use Logistics\DB\User;
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

        $response = $this->get(route('tenant.admin.company.edit'), []);
        $response->assertRedirect(route('tenant.auth.get.login'));
    }

    /** @test */
    public function admin_can_update_the_company()
    {
        //$this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        $response = $this->actingAs($admin)->get(route('tenant.admin.company.edit'));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.company.edit');

        $response = $this->actingAs($admin)->post(route('tenant.admin.company.update'), [
            '_method' => 'PATCH',
            'name' => 'Company Name Updated',
            'ruc' => 'RUC',
            'dv' => 'DV',
            'telephones' => '555-3454,545-5435',
            'emails' => ' contact@tenant.com, sales@tenant.com ',
            'address' => 'In the middle of nowhere',
            'lang' => 'es',
        ]);

        $this->assertDatabaseHas('tenants', [
            'name' => 'Company Name Updated',
            'domain' => 'https://middleton-services.test',
            'status' => 'A',
            'ruc' => 'RUC',
            'dv' => 'DV',
            'telephones' => '555-3454,545-5435',
            'emails' => 'contact@tenant.com, sales@tenant.com',
            'address' => 'In the middle of nowhere',
            'lang' => 'es',
        ]);

        $this->assertNotNull(view()->shared('tenant'));
        $this->assertEquals('Company Name Updated', view()->shared('tenant')->name);
            
        $response->assertSessionHas(['flash_success']);
        $response->assertRedirect(route('tenant.admin.company.edit'));
    }

    /** @test */
    public function admin_cannot_edit_the_domain_nor_the_status_of_the_company()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        $response = $this->actingAs($admin)->post(route('tenant.admin.company.update'), [
            '_method' => 'PATCH',
            'name' => 'Company Name Updated',
            'ruc' => 'RUC',
            'dv' => 'DV',
            'telephones' => '555-3454,545-5435',
            'emails' => '555-3454,545-5435',
            'address' => 'In the middle of nowhere',
            'lang' => 'es',
            'domain' => 'https://other-domain.test',
            'status' => 'I',
        ]);

        $response->assertSessionHasErrors(['domain', 'status']);
        $response->assertRedirect(route('tenant.admin.company.edit'));
        $this->assertEquals('https://middleton-services.test', $tenant->fresh()->first()->domain);
        $this->assertEquals('Middleton Services S.A.', $tenant->fresh()->first()->name);
    }

    /** @test */
    public function admin_cannot_edit_the_company_with_invalid_inputs()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        $response = $this->actingAs($admin)->post(route('tenant.admin.company.update'), [
            '_method' => 'PATCH',
            'name' => 'C',
            'telephones' => '555,543',
            'emails' => '555,545',
            'address' => '123',
            'lang' => 'xx',
        ]);

        $response->assertSessionHasErrors(['name', 'telephones', 'emails', 'address', 'lang', ]);
        $response->assertRedirect(route('tenant.admin.company.edit'));
        $this->assertEquals('https://middleton-services.test', ($tenant = $tenant->fresh()->first())->domain);
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

        $response = $this->actingAs($admin)->post(route('tenant.admin.company.update'), [
            'name' => 'Company Name Updated',
            'ruc' => 'RUC',
            'dv' => 'DV',
            'telephones' => '555-3454,545-5435',
            'emails' => ' contact@tenant.com, sales@tenant.com ',
            'address' => 'In the middle of nowhere',
            'lang' => 'es',
            'logo' => $file,
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

        $response = $this->actingAs($admin)->post(route('tenant.admin.company.update'), [
            'name' => 'Company Name Updated',
            'ruc' => 'RUC',
            'dv' => 'DV',
            'telephones' => '555-3454,545-5435',
            'emails' => ' contact@tenant.com, sales@tenant.com ',
            'address' => 'In the middle of nowhere',
            'lang' => 'es',
            'logo' => $file,
            '_method' => 'PATCH',
        ]);

        $tenant = $tenant->fresh()->first();

        Storage::disk('public')->assertMissing("tenant/{$tenant->id}/images/logos/logo.png");
        $this->assertEquals("tenant/{$tenant->id}/images/logos/{$file->hashName()}", $tenant->logo);
        Storage::disk('public')->assertExists("tenant/{$tenant->id}/images/logos/{$file->hashName()}");
    }
}
