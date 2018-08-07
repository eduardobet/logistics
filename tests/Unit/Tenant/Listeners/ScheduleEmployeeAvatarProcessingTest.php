<?php

namespace Tests\Feature\Tenant\Listeners;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Branch;
use Illuminate\Support\Facades\Queue;
use Logistics\Jobs\Tenant\ProcessAvatar;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Logistics\Events\Tenant\EmployeeAvatarAdded;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScheduleEmployeeAvatarProcessingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_queues_a_job_to_process_the_employee_avatar()
    {
        $this->withoutExceptionHandling();

        Queue::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $employee = factory(User::class)->states('employee')->create(['tenant_id' => $tenant->id, "avatar" => "tenant/{$tenant->id}/images/avatars/avatar.png"]);

        EmployeeAvatarAdded::dispatch($employee);

        Queue::assertPushed(ProcessAvatar::class, function ($job) use ($employee) {
            return $job->employee->is($employee);
        });
    }
}
