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
use Logistics\Jobs\Tenant\SendClientWelcomeEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Logistics\Events\Tenant\ClientWasCreatedEvent;

class WelcomeClientEmailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function email_has_the_correct_data()
    {
        $tenant = factory(TenantModel::class)->create(['lang' => 'en']);
        $tenant->remoteAddresses()->createMany([
            ['type' => 'A', 'address' => 'In the middle of remote air', 'telephones' => '555-5555', 'status' => 'A', ],
            ['type' => 'M', 'address' => 'In the middle of remote maritimes', 'telephones' => '555-5555', 'status' => 'A', ],
        ]);

        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'manual_id' => 1, ]);
        $box = factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);

        $email = new WelcomeClientEmail($tenant, $client);
        $data = $email->buildViewData();
        $content = $this->render($email);
        $air =$tenant->remoteAddresses->where('type', 'A')->first();
        $maritime =$tenant->remoteAddresses->where('type', 'M')->first();

        $this->assertTrue($data['tenant']->is($tenant));
        $this->assertTrue($data['client']->is($client));
        $this->assertEquals("Welcome {$client->full_name}", $email->build()->subject);

        $this->assertContains("Hello {$client->full_name}", $content);
        $this->assertContains("welcome to {$branch->name}", $content);
        $this->assertContains("Below, your box information:", $content);
        $this->assertContains("Box number:", $content);
        $this->assertContains("{$box->branch_code}{$client->id}", $content);
        $this->assertContains("This is the address you should use when making your purchases:", $content);
        $this->assertContains("For Aerial Shipments:", $content);
        $this->assertContains("{$client->first_name} {$box->branch_code}{$client->id} {$client->last_name}", $content);
        $this->assertContains("{$air->address}", $content);
        $this->assertContains("{$air->telephones}", $content);

        $this->assertContains("For Maritime Shipments:", $content);
        $this->assertContains("{$client->first_name} {$box->branch_code}{$client->id} {$client->last_name}", $content);
        $this->assertContains("{$maritime->address}", $content);
        $this->assertContains("{$maritime->telephones}", $content);

        $this->assertContains("For tracking:", $content);
        $this->assertContains(route('tenant.tracking.get', $tenant->domain), $content);

        $this->assertContains("For misidentified packages:", $content);
        $this->assertContains(route('tenant.misidentified-package.create', $tenant->domain), $content);

        $this->assertContains("Remember your purchases must always contain your box:", $content);
        $this->assertContains("{$box->branch_code}{$client->id}", $content);
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

        dispatch(new SendClientWelcomeEmail($tenant, $client));

        Mail::assertSent(WelcomeClientEmail::class, function ($mail) use ($tenant, $client) {
            return $mail->hasTo($client->email)
                && $mail->tenant->is($tenant)
                && $mail->client->is($client);
        });
    }
}
