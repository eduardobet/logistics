<?php

namespace Tests\Feature\Tenant\Tracking;

use Tests\TestCase;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TrackingValidationTest extends TestCase
{ 
    use RefreshDatabase;

    public function test_recaptcha_token_is_required()
    {
        $this->withoutExceptionHandling();
        
        $tenant = factory(TenantModel::class)->create();

        $response = $this->post(route('tenant.tracking.post', [$tenant->domain, ]), [], $this->headers());

        $response->assertStatus(500);
    }

    public function test_term_is_required()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();

        $response = $this->post(route('tenant.tracking.post', [$tenant->domain, ]), [
            'token' => 'g-recaptcha-response'
        ], $this->headers());

        $response->assertStatus(500);
    }
}
