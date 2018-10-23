<?php

namespace Tests\Feature\Tenant\Listeners;

use Tests\TestCase;
use Logistics\DB\Tenant\Branch;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\Events\Tenant\BranchLogoAdded;
use Logistics\Jobs\Tenant\ProcessBranchLogo;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScheduleBranchLogoProcessingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_queues_a_job_to_process_the_branch_logo()
    {
        $this->withoutExceptionHandling();

        Queue::fake();
        
        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)
            ->create(['tenant_id' => $tenant->id, "logo" => "tenant/1/images/logos/logo.png"]);

        BranchLogoAdded::dispatch($branch);

        Queue::assertPushed(ProcessBranchLogo::class, function ($job) use ($branch) {
            return $job->branch->is($branch);
        });
    }
}
