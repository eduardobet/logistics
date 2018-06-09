<?php

namespace Tests\Unit\Tenant\Client;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Box;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Client;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\Mail\Tenant\WelcomeClientEmail;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Logistics\Events\Tenant\ClientWasCreatedEvent;

class WelcomeClientEmailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function email_has_the_correct_data()
    {
        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, ]);
        $box = factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);

        $email = new WelcomeClientEmail($tenant, $client);
        $data = $email->buildViewData();
        $content = $this->render($email);

        $this->assertTrue($data['tenant']->is($tenant));
        $this->assertTrue($data['client']->is($client));
        $this->assertEquals("Welcome {$client->full_name}", $email->build()->subject);

        $this->assertContains("Hello {$client->full_name}", $content);
        $this->assertContains("welcome to {$tenant->name}", $content);
        $this->assertContains("Below, your box information:", $content);
        $this->assertContains("Box number: {$box->branch_code}{$client->id}", $content);
    }

    /** @test */
    public function it_sends_a_welcome_email_to_the_client()
    {
        $this->withoutExceptionHandling();

        Mail::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, ]);
        $box = factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);

        event(new ClientWasCreatedEvent($tenant, $client));

        Mail::assertSent(WelcomeClientEmail::class, function ($mail) use ($tenant, $client) {
            return $mail->hasTo($client->email)
                && $mail->tenant->is($tenant)
                && $mail->client->is($client);
        });
    }
}
