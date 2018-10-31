<?php

namespace Tests\Feature\Tenant\Tracking;

use Tests\TestCase;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use AlbertCht\InvisibleReCaptcha\Facades\InvisibleReCaptcha;

class TrackingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function the_tracking_page_returns_200()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(Tenant::class)->create();

        $response = $this->get(route('tenant.tracking.get', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.tracking.index');
    }

    /** @test */
    public function it_returns_the_apropriate_flag_when_the_reca_is_misidentified()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(Tenant::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch Name', ]);

        $cargoEntryA = $tenant->cargoEntries()->create([
            'branch_id' => $branch->id,
            'trackings' => '123654865\n68545554\n36995524',
            'type' => 'M',
        ]);

        InvisibleReCaptcha::shouldReceive('verifyResponse')
            ->once()
            ->andReturn(true);

        $response = $this->post(route('tenant.tracking.post', [$tenant->domain, ]), [
            'g-recaptcha-response' => 'g-recaptcha-response',
            'term' => '123654865',
        ], $this->headers());

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'misindentified' => true,
            'data' => []
        ]);
    }

    /** @test */
    public function it_successfully_returns_the_correct_tracking_data()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(Tenant::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch Name', ]);

        $cargoEntryA = $tenant->cargoEntries()->create([
            'branch_id' => $branch->id,
            'trackings' => '123654865\n68545554\n36995524',
        ]);

        InvisibleReCaptcha::shouldReceive('verifyResponse')
            ->once()
            ->andReturn(true);

        $response = $this->post(route('tenant.tracking.post', [$tenant->domain, ]), [
            'g-recaptcha-response' => 'g-recaptcha-response',
            'term' => '123654865',
        ], $this->headers());

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'data' => []
        ]);
    }
}
