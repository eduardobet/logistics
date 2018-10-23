<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use Logistics\DB\Tenant\Branch;
use Illuminate\Support\Facades\Storage;
use Logistics\Jobs\Tenant\ProcessBranchLogo;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProcessBranchLogoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_resizes_the_branch_logo_to_600px_wide()
    {
        $this->withoutExceptionHandling();
        Storage::fake('public');

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        
        Storage::disk('public')->put(
            "tenant/{$tenant->id}/images/logos/logo.png",
            file_get_contents(storage_path('fixtures/full-size-image.png'))
        );

        $branch = $branch->fresh()->first();
        $branch->logo = "tenant/{$tenant->id}/images/logos/logo.png";
        $branch->save();

        ProcessBranchLogo::dispatch($branch);
        $prefix = $this->optimized_for();
        $resizedImage = Storage::disk('public')->get("tenant/{$tenant->id}/images/logos/logo.png");
        list($width, $height) = getimagesizefromstring($resizedImage);
        $this->assertEquals(600, $width);
        $this->assertEquals(776, $height);
    }

    /** @test */
    public function it_optimizes_the_branch_logo()
    {
        $this->withoutExceptionHandling();
        Storage::fake('public');

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);

        Storage::disk('public')->put(
            "tenant/{$tenant->id}/images/logos/logo.png",
            file_get_contents(storage_path('fixtures/small-unoptimized-image.png'))
        );

        $branch = $branch->fresh()->first();
        $branch->logo = "tenant/{$tenant->id}/images/logos/logo.png";
        $branch->save();

        ProcessBranchLogo::dispatch($branch);
        $prefix = $this->optimized_for();
        $optimizedImageSize = Storage::disk('public')->size("tenant/{$tenant->id}/images/logos/logo.png");
        $originalSize = filesize(storage_path('fixtures/small-unoptimized-image.png'));
        $this->assertLessThan($originalSize, $optimizedImageSize);
    }

    /** @test */
    public function it_does_not_resize_logo_with_less_than_600px_wide()
    {
        $this->withoutExceptionHandling();
        Storage::fake('public');

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        
        Storage::disk('public')->put(
            "tenant/{$tenant->id}/images/logos/logo.png",
            file_get_contents(storage_path('fixtures/less-than-600px-wide-image.png'))
        );
        $branch = $branch->fresh()->first();
        $branch->logo = "tenant/{$tenant->id}/images/logos/logo.png";
        $branch->save();

        ProcessBranchLogo::dispatch($branch);
        $prefix = $this->optimized_for();
        $resizedImage = Storage::disk('public')->get("tenant/{$tenant->id}/images/logos/logo.png");
        list($width, $height) = getimagesizefromstring($resizedImage);
        $this->assertEquals(386, $width);
        $resizedImageContents = Storage::disk('public')->get("tenant/{$tenant->id}/images/logos/logo.png");
        $controlImageContents = file_get_contents(storage_path("fixtures/less-than-600px-wide-image.png"));
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
