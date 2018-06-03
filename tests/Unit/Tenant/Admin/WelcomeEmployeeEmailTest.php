<?php

namespace Tests\Unit\Tenant\Admin;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Branch;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Logistics\Mail\Tenant\WelcomeEmployeeEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Logistics\Events\Tenant\EmployeeWasCreatedEvent;

class WelcomeEmployeeEmailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function email_has_the_correct_data()
    {
        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        $email = new WelcomeEmployeeEmail($tenant, $admin);
        $data = $email->buildViewData();

        $this->assertTrue($data['tenant']->is($tenant));
        $this->assertTrue($data['employee']->is($admin));
        $this->assertEquals("Welcome", $email->build()->subject);
    }

    /** @test */
    public function it_sends_a_welcome_email_to_the_employee()
    {
        $this->withoutExceptionHandling();

        Mail::fake();

        $tenant = factory(TenantModel::class)->create();
        $employee = factory(User::class)->states('employee')->create(['tenant_id' => $tenant->id, ]);

        event(new EmployeeWasCreatedEvent($tenant, $employee));

        Mail::assertSent(WelcomeEmployeeEmail::class, function ($mail) use ($tenant, $employee) {
            return $mail->hasTo($employee->email)
                && $mail->tenant->is($tenant)
                && $mail->employee->is($employee);
        });
    }
}
