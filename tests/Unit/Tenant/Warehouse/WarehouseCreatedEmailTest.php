<?php

namespace Tests\Unit\Tenant\Warehouse;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Box;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Client;
use Illuminate\Support\Facades\Mail;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Logistics\Mail\Tenant\WarehouseCreatedEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Logistics\Jobs\Tenant\SendWarehouseCreatedEmail;

class WarehouseCreatedEmailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function email_has_the_correct_data()
    {
        $tenant = factory(TenantModel::class)->create(['lang' => 'en']);
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id, ]);

        $client = factory(Client::class)
            ->create(['tenant_id' => $tenant->id, 'pay_volume' => false, 'special_rate' => false, 'vol_price' => 2.00, 'real_price' => 1.50, ]);
        factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branchB->id,
            'branch_code' => $branchB->code,
        ]);

        $warehouse = $tenant->warehouses()->create([
            'branch_from' => $branch->id,
            'branch_to' => $branchB->id,
            'client_id' => $client->id,
            'reference' => 'The reference',
            'type' => 'A',
            'trackings' => '12345,234434,55645',
            'qty' => 2,
            'tot_packages' => 2,
            'tot_weight' => 2,
        ]);

        $email = new WarehouseCreatedEmail($tenant, $warehouse);
        $data = $email->buildViewData();
        $content = $this->render($email);

        $this->assertTrue($data['tenant']->is($tenant));
        $this->assertTrue($data['warehouse']->is($warehouse));

        $this->assertContains("Hello {$branch->code}{$client->manual_id_dsp} / {$client->full_name}", $content);
        $this->assertContains( "Your packages have been received. You may pick them up in 24 hours.", $content);
        $this->assertContains("Details:", $content);
        $this->assertContains("Total packages: {$warehouse->tot_packages}", $content);
        $this->assertContains("Total weight: {$warehouse->tot_weight}", $content);
        $this->assertContains("Trackings: {$warehouse->trackings}", $content);
    }

    /** @test */
    public function it_sends_the_warehouse_creation_notification()
    {
        $this->withoutExceptionHandling();

        Mail::fake();

        $tenant = factory(TenantModel::class)->create(['lang' => 'en']);
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id, ]);

        $client = factory(Client::class)
            ->create(['tenant_id' => $tenant->id, 'pay_volume' => false, 'special_rate' => false, 'vol_price' => 2.00, 'real_price' => 1.50, ]);
        factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branchB->id,
            'branch_code' => $branchB->code,
        ]);

        $warehouse = $tenant->warehouses()->create([
            'branch_from' => $branch->id,
            'branch_to' => $branchB->id,
            'client_id' => $client->id,
            'reference' => 'The reference',
            'type' => 'A',
            'trackings' => '12345,234434,55645',
            'qty' => 2,
            'tot_packages' => 2,
            'tot_weight' => 2,
        ]);

        dispatch(new SendWarehouseCreatedEmail($tenant, $warehouse));

        Mail::assertSent( WarehouseCreatedEmail::class, function ($mail) use ($tenant, $client, $warehouse) {
            return $mail->hasTo($client->email)
                && $mail->tenant->is($tenant)
                && $mail->warehouse->is($warehouse);
        });
    }
}
