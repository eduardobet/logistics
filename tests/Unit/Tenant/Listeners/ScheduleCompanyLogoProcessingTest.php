<?php

namespace Tests\Feature\Tenant\Listeners;

use Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use Logistics\Jobs\Tenant\ProcessLogo;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\Events\Tenant\CompanyLogoAdded;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScheduleCompanyLogoProcessingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_queues_a_job_to_process_the_company_logo()
    {
        $this->withoutExceptionHandling();

        Queue::fake();

        $tenant = factory(TenantModel::class)
            ->create(["logo" => "tenant/1/images/logos/logo.png"]);

        CompanyLogoAdded::dispatch($tenant);

        Queue::assertPushed(ProcessLogo::class, function ($job) use ($tenant) {
            return $job->tenant->is($tenant);
        });
    }
}
