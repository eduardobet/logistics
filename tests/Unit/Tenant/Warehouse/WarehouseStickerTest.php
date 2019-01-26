<?php

namespace Tests\Unit\Tenant\Warehouse;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Box;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Client;
use Logistics\DB\Tenant\Mailer;
use Logistics\DB\Tenant\Country;
use Logistics\DB\Tenant\Invoice;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WarehouseStickerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_like_this_tenant_user()
    {
        // $this->withoutExceptionHandling();
        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.warehouse.print-sticker', [$tenant->domain, 1]), []);
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
    }

    /** @test */
    public function it_successfully_generates_the_sticker()
    {
        $this->withoutExceptionHandling();

        $country = factory(Country::class)->create();
        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $branchFrom = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch from',  ]);
        $branchTo = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);
        $mailer = factory(Mailer::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branchFrom->id,]);

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'pay_volume' => true, 'vol_price' => 2.00, 'branch_id' => $branchTo->id, 'manual_id' => 1, 'first_name' => 'First', 'last_name' => 'Last', ]);

        $box = factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branchTo->id,
            'branch_code' => $branchTo->code,
        ]);

        $warehouse = $tenant->warehouses()->create([
            'type' => 'A',
            'branch_from' => $branchFrom->id,
            'branch_to' => $branchTo->id,
            'client_id' => $client->id,
            'mailer_id' => $mailer->id,
            'trackings' => '1234',
            'reference' => 'The reference',
            'qty' => 1,
        ]);

        $invoice = Invoice::create([
            'tenant_id' => $tenant->id,
            'warehouse_id' => $warehouse->id,
            'branch_id' => $warehouse->branch_to,
            'client_id' => $client->id,
            'client_name' => 'The Name of the client',
            'client_email' => 'email@client.com',
            'volumetric_weight' => 21,
            'real_weight' => 23,
            'total' => $client->vol_price * 21,
        ]);

        $detailA = $invoice->details()->create([
            'qty' => 1,
            'type' => 1,
            'length' => 10,
            'width' => 10,
            'height' => 10,
            'vol_weight' => 8,
            'real_weight' => 9,
        ]);

        $detailB = $invoice->details()->create([
            'qty' => 1,
            'type' => 1,
            'length' => 12,
            'width' => 12,
            'height' => 12,
            'vol_weight' => 13,
            'real_weight' => 14,
        ]);

        $response = $this->actingAs($admin)->get(route('tenant.warehouse.print-sticker', [$tenant->domain, $warehouse->id]), []);
        $sticker = $response->getContent();

        $this->assertContains($mailer->name, $sticker);
        $this->assertContains($branchTo->name, $sticker);
        $this->assertContains($branchTo->address, $sticker);

        // packages details
        $this->assertContains("{$detailA->vol_weight}.00LBS", $sticker);
        $this->assertContains("{$detailA->length}.0x{$detailA->width}.0x{$detailA->height}.0", $sticker);
        $this->assertContains("({$detailA->qty})", $sticker);
        $this->assertContains("{$detailB->vol_weight}.00LBS", $sticker);
        $this->assertContains("{$detailB->length}.0x{$detailB->width}.0x{$detailB->height}.0", $sticker);
        $this->assertContains("({$detailB->qty})", $sticker);

        $this->assertContains("{$box->branch_code}{$client->manual_id} / {$client->full_name}      \${$invoice->total}.0", $sticker);

        $this->assertContains("{$warehouse->id}", $sticker);
        $this->assertContains("{$country->iata}", $sticker);
        $this->assertContains("AIR", $sticker);
        $this->assertContains("barcode", $sticker);
        $this->assertContains("{$warehouse->id}", $sticker);
        $this->assertContains($admin->full_name, $sticker);
    }
}
