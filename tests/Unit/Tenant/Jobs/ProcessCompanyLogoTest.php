<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use Logistics\Jobs\Tenant\ProcessLogo;
use Illuminate\Support\Facades\Storage;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProcessCompanyLogoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_resizes_the_company_logo_to_600px_wide()
    {
        $this->withoutExceptionHandling();
        Storage::fake('public');

        $tenant = factory(TenantModel::class)->create();
        
        Storage::disk('public')->put(
            "tenant/{$tenant->id}/images/logos/logo.png",
            file_get_contents(storage_path('fixtures/full-size-image.png'))
        );

        $tenant = $tenant->fresh()->first();
        $tenant->logo = "tenant/{$tenant->id}/images/logos/logo.png";
        $tenant->save();

        Processlogo::dispatch($tenant);
        $prefix = $this->optimized_for();
        $resizedImage = Storage::disk('public')->get("tenant/{$tenant->id}/images/logos/logo.png");
        list($width, $height) = getimagesizefromstring($resizedImage);
        $this->assertEquals(600, $width);
        $this->assertEquals(776, $height);
    }

    /** @test */
    public function it_optimizes_the_company_logo()
    {
        $this->withoutExceptionHandling();
        Storage::fake('public');

        $tenant = factory(TenantModel::class)->create();

        Storage::disk('public')->put(
            "tenant/{$tenant->id}/images/logos/logo.png",
            file_get_contents(storage_path('fixtures/small-unoptimized-image.png'))
        );

        $tenant = $tenant->fresh()->first();
        $tenant->logo = "tenant/{$tenant->id}/images/logos/logo.png";
        $tenant->save();

        Processlogo::dispatch($tenant);
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

        Storage::disk('public')->put(
            "tenant/{$tenant->id}/images/logos/logo.png",
            file_get_contents(storage_path('fixtures/less-than-600px-wide-image.png'))
        );
        $tenant = $tenant->fresh()->first();
        $tenant->logo = "tenant/{$tenant->id}/images/logos/logo.png";
        $tenant->save();

        Processlogo::dispatch($tenant);
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
