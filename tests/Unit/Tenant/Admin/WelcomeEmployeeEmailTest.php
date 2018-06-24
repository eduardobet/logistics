<?php

namespace Tests\Unit\Tenant\Admin;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Branch;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Logistics\Mail\Tenant\WelcomeEmployeeEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Logistics\Events\Tenant\EmployeeWasCreatedEvent;

class WelcomeEmployeeEmailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function email_has_the_correct_data()
    {
        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $employee = factory(User::class)->states('employee')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$admin->id]);
        $employee->branches()->sync([$employee->id]);

        $email = new WelcomeEmployeeEmail($tenant, $employee);
        $data = $email->buildViewData();
        $content = $this->render($email);
        $type =  $employee->type == 'A' ? 'admin' : 'employee';

        $this->assertTrue($data['tenant']->is($tenant));
        $this->assertTrue($data['employee']->is($employee));

        $this->assertEquals("Welcome {$employee->full_name}", $email->build()->subject);
        $this->assertContains("Hello {$employee->full_name}", $content);
        $this->assertContains("welcome to {$tenant->name}", $content);
        $this->assertContains("Please click the following link to activate your account.", $content);
        $this->assertContains(URL::signedRoute('tenant.employee.get.unlock', [$employee->email, $employee->token]), $content);
        $this->assertContains("Some other interesting links:", $content);
        $this->assertContains(route('tenant.home'), $content);
        $this->assertContains(route("tenant.{$type}.dashboard"), $content);
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
