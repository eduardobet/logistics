<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Branch;
use Illuminate\Support\Facades\Storage;
use Logistics\Jobs\Tenant\ProcessAvatar;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProcessEmployeeAvatarTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_resizes_the_empployee_avatar_to_200px_wide()
    {
        $this->withoutExceptionHandling();
        Storage::fake('public');

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        
        Storage::disk('public')->put(
            "tenant/{$tenant->id}/images/avatars/avatar.png",
            file_get_contents(storage_path('fixtures/full-size-image.png'))
        );
        $employee = factory(User::class)->states('employee')->create(['tenant_id' => $tenant->id, "avatar" => "tenant/{$tenant->id}/images/avatars/avatar.png"]);

        ProcessAvatar::dispatch($employee);
        $prefix = $this->optimized_for();
        $resizedImage = Storage::disk('public')->get("tenant/{$tenant->id}/images/avatars/avatar.png");
        list($width, $height) = getimagesizefromstring($resizedImage);
        $this->assertEquals(200, $width);
        $this->assertEquals(259, $height);
    }

    /** @test */
    public function it_optimizes_the_employee_avatar()
    {
        $this->withoutExceptionHandling();
        Storage::fake('public');

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);

        Storage::disk('public')->put(
            "tenant/{$tenant->id}/images/avatars/avatar.png",
            file_get_contents(storage_path('fixtures/small-unoptimized-image.png'))
        );
        $employee = factory(User::class)->states('employee')->create(['tenant_id' => $tenant->id, "avatar" => "tenant/{$tenant->id}/images/avatars/avatar.png"]);


        ProcessAvatar::dispatch($employee);
        $prefix = $this->optimized_for();
        $optimizedImageSize = Storage::disk('public')->size("tenant/{$tenant->id}/images/avatars/avatar.png");
        $originalSize = filesize(storage_path('fixtures/small-unoptimized-image.png'));
        $this->assertLessThan($originalSize, $optimizedImageSize);
    }

    /** @test */
    public function it_does_not_resize_logo_with_less_than_200px_wide()
    {
        $this->withoutExceptionHandling();
        Storage::fake('public');

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);

        Storage::disk('public')->put(
            "tenant/{$tenant->id}/images/avatars/avatar.png",
            file_get_contents(storage_path('fixtures/less-than-200px-wide-image.png'))
        );
        $employee = factory(User::class)->states('employee')->create(['tenant_id' => $tenant->id, "avatar" => "tenant/{$tenant->id}/images/avatars/avatar.png"]);

        ProcessAvatar::dispatch($employee);
        $prefix = $this->optimized_for();
        $resizedImage = Storage::disk('public')->get("tenant/{$tenant->id}/images/avatars/avatar.png");
        list($width, $height) = getimagesizefromstring($resizedImage);
        $this->assertEquals(184, $width);
        $resizedImageContents = Storage::disk('public')->get("tenant/{$tenant->id}/images/avatars/avatar.png");
        $controlImageContents = file_get_contents(storage_path("fixtures/less-than-200px-wide-image.png"));
        $this->assertEquals($controlImageContents, $resizedImageContents);
    }

    private function optimized_for()
    {
        if (strtolower(PHP_SHLIB_SUFFIX) === 'dll') {
            return 'win';
        }

        return 'nix';
    }
}
