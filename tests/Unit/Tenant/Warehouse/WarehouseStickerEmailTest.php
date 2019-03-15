<?php

namespace Tests\Unit\Tenant\Warehouse;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Box;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Client;
use Logistics\DB\Tenant\Mailer;
use Logistics\DB\Tenant\Invoice;
use Illuminate\Support\Facades\Storage;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Logistics\Mail\Tenant\WarehouseReceiptEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WarehouseStickerEmailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function email_has_the_correct_data()
    {
        $this->withoutExceptionHandling();

        Storage::fake('whreceipts');

        $tenant = factory(TenantModel::class)->create(['lang' => 'en',]);
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id,  ]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', 'initial' => 'BR' ]);
        $mailer = factory(Mailer::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id,]);

        $this->actingAs($admin);

        \Gate::define('edit-warehouse', function ($admin) {
            return true;
        });

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'first_name' => 'The', 'last_name' => 'Client', ]);

        $box = factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);

        $warehouse = $tenant->warehouses()->create([
            'branch_to' => $branchB->id,
            'branch_from' => $branch->id,
            'client_id' => $client->id,
            'mailer_id' => $mailer->id,
            'trackings' => '1234',
            'reference' => 'The reference',
            'qty' => 1,
            'type' => 'A',
        ]);

        $invoice = Invoice::create([
            'tenant_id' => $tenant->id,
            'warehouse_id' => $warehouse->id,
            'client_id' => $client->id,
            'branch_id' => $warehouse->branch_to,
            'created_by_code' => $admin->id,
            'updated_by_code' => $admin->id,
            'client_name' => 'The Name of the client',
            'client_email' => 'email@client.com',
            'volumetric_weight' => 8,
            'real_weight' => 9,
            'manual_id' => 9,
            'total' => $mailer->vol_price * 8,
        ]);

        $invoice->details()->create([
            'qty' => 1,
            'type' => 1,
            'length' => 10,
            'width' => 10,
            'height' => 10,
            'vol_weight' => 8,
            'real_weight' => 9,
        ]);

        $data = [
            'warehouse' => $warehouse,
            'branchTo' => $tenant->branches()->select(['tenant_id', 'id', 'name', 'initial', 'address', 'telephones'])->find($warehouse->branch_to),
            'mailer' => $tenant->mailers()->select(['tenant_id', 'id', 'name'])->find($warehouse->mailer_id),
            'client' => $tenant->clients()
                ->with('branch')
                ->select(['tenant_id', 'id', 'first_name', 'last_name', 'address', 'email', 'telephones', 'branch_id', 'manual_id'])
                ->find($warehouse->client_id),
            'invoice' => $warehouse->invoice()
                ->with('details')->first(),
        ];

        $email = new WarehouseReceiptEmail($tenant, $data);

        $content = $this->render($email);

        $this->assertEquals("Warehouse receipt #{$branchB->initial}-{$warehouse->manual_id_dsp}", $email->build()->subject);

        $this->assertContains("Hello", $content);
        $this->assertContains("{$client->full_name} / {$box->branch_code}{$client->manual_id_dsp}", $content);
        $this->assertContains("Please check your warehouse receipt in attachment.", $content);
        $this->assertContains("If you can't see the attachment, please click the following link:", $content);
        $this->assertContains($email->viewData['path'], $content);
    }
}
