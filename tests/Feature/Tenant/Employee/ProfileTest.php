<?php

namespace Tests\Feature\Tenant\Employee;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Branch;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_employee()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.employee.profile.edit'));
        $response->assertRedirect(route('tenant.auth.get.login'));
    }

    /** @test */
    public function employee_cannot_update_status_is_main_admin_branch_type()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branchA = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch Name B', ]);
        $employee = factory(User::class)->states('employee')->create(['tenant_id' => $tenant->id, 'status' => 'L', ]);
        $employee->branches()->sync([$branchA->id]);

        $response = $this->actingAs($employee)->post(route('tenant.employee.profile.update'), [
            'first_name' => 'Employee f name update',
            'last_name' => 'Employee l name update',
            'email' => $employee->email,
            'type' => 'A',
            'status' => 'A',
            'branches' => [$branchB->id],
            'is_main_admin' => true,
            '_method' => 'PATCH',
        ]);

        $response->assertRedirect(route('tenant.employee.profile.edit'));
        $response->assertSessionHasErrors(['status', 'is_main_admin', 'branches', 'type']);
        $this->assertDatabaseMissing('users', [
            'type' => 'A',
            'status' => 'A',
        ]);
    }

    /** @test */
    public function employee_can_update_his_basic_information()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $employee = factory(User::class)->states('employee')->create(['tenant_id' => $tenant->id,]);
        $employee->branches()->sync([$branch->id]);

        $response = $this->actingAs($employee)->get(route('tenant.employee.profile.edit'));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.employee.profile');
        $response->assertViewHas(['employee']);

        $response = $this->actingAs($employee)->post(route('tenant.employee.profile.update'), [
            'first_name' => 'Employee f name update',
            'last_name' => 'Employee l name update',
            'pid' => 'PID',
            'telephones' => '555-5555',
            'address' => 'In the middle of nowhere',
            'notes' => 'Some notes about the employee',
            '_method' => 'PATCH',
        ]);

        $this->assertDatabaseHas('users', [
            'first_name' => 'Employee f name update',
            'last_name' => 'Employee l name update',
            'email' => $employee->email,
            'full_name' => 'Employee f name update Employee l name update' ,
            'pid' => 'PID',
            'telephones' => '555-5555',
            'type' => 'E',
            'status' => 'A',
            'is_main_admin' => false,
            'address' => 'In the middle of nowhere',
            'notes' => 'Some notes about the employee',
        ]);
        
        $response->assertRedirect(route('tenant.employee.profile.edit'));
    }

    /** @test */
    public function employee_can_upload_an_avatar()
    {
        $this->withoutExceptionHandling();

        Storage::fake('public');

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $employee = factory(User::class)->states('employee')->create(['tenant_id' => $tenant->id,]);
        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($employee)->post(route('tenant.employee.profile.update'), [
            'first_name' => 'Employee f name update',
            'last_name' => 'Employee l name update',
            'avatar' => $file,
            '_method' => 'PATCH',
        ]);

        $employee = $employee->fresh()->first();

        $this->assertEquals("tenant/{$tenant->id}/images/avatars/{$file->hashName()}", $employee->avatar);
        Storage::disk('public')->assertExists("tenant/{$tenant->id}/images/avatars/{$file->hashName()}");
    }

    /** @test */
    public function employee_old_avatar_is_removed()
    {
        $this->withoutExceptionHandling();

        Storage::fake('public');

        $file = UploadedFile::fake()->image('avatar.jpg');
        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);


        Storage::disk('public')->put(
            "tenant/{$tenant->id}/images/avatars/avatar.png",
            file_get_contents(storage_path('fixtures/less-than-200px-wide-image.png'))
        );

        $employee = factory(User::class)->states('employee')
            ->create(['tenant_id' => $tenant->id, 'avatar' => "tenant/{$tenant->id}/images/avatars/avatar.png" ]);

        $response = $this->actingAs($employee)->post(route('tenant.employee.profile.update'), [
            'first_name' => 'Employee f name update',
            'last_name' => 'Employee l name update',
            'avatar' => $file,
            '_method' => 'PATCH',
        ]);

        $employee = $employee->fresh()->first();

        Storage::disk('public')->assertMissing("tenant/{$tenant->id}/images/avatars/avatar.png");
        $this->assertEquals("tenant/{$tenant->id}/images/avatars/{$file->hashName()}", $employee->avatar);
        Storage::disk('public')->assertExists("tenant/{$tenant->id}/images/avatars/{$file->hashName()}");
    }
}
