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

class InvoicePrintingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_like_this_tenant_user()
    {
        // $this->withoutExceptionHandling();
        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.invoice.print-invoice', [$tenant->domain, 1]), []);
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
    }

    /** @test */
    public function it_successfully_generates_the_printed_invoice()
    {
        $this->withoutExceptionHandling();

        $country = factory(Country::class)->create();
        $tenant = factory(TenantModel::class)->create(['country_id' => $country->id, 'logo' => "tenant/1/images/logos/logo.png"]);
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);
        $tenant->conditions()->createMany([
            ['type' => 'I', 'content' => 'The conditions for invoices', 'status' => 'A', ],
        ]);


        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id, ]);

        $this->actingAs($admin);

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'pay_volume' => true, 'vol_price' => 2.00, 'manual_id' => 1, ]);

        $box = factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);

        $branch->productTypes()->create([
            'name' => 'Card commission', 'status' => 'A'
        ]);

        $branch->productTypes()->create([
            'name' => 'Online shopping', 'status' => 'A'
        ]);

        $invoice = $tenant->invoices()->create([
            'branch_id' => $branch->id,
            'client_id' => $client->id,
            'total' => 160,
        ]);

        $detailA = $invoice->details()->create([
            'qty' => 1,
            'type' => 1,
            'description' => 'Buying from amazon',
            'id_remote_store' => '122452222',
            'total' => 100,
        ]);

        $detailB = $invoice->details()->create([
            'qty' => 1,
            'type' => 2,
            'description' => 'Buying from ebay',
            'id_remote_store' => '10448796566',
            'total' => 60,
        ]);

        $payment = $invoice->payments()->create([
            'tenant_id' => $invoice->tenant_id,
            'amount_paid' => 80,
            'payment_method' => 1,
            'payment_ref' => 'The client paid $80.00',
            'is_first' => true,
        ]);

        $response = $this->get(route('tenant.invoice.print-invoice', [$tenant->domain, $invoice->id]), []);
        $response->assertViewIs('tenant.invoice.printing');

        $content = $response->getContent();

        $this->assertContains("{$branch->name}", $content);
        $this->assertContains("{$branch->ruc} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DV {$branch->dv}", $content);
        $this->assertContains("{$branch->address}", $content);
        $this->assertContains("{$branch->telephones}", $content);


        $this->assertContains("Client:", $content);
        $this->assertContains("{$client->full_name} / {$box->branch_code}{$client->manual_id_dsp}", $content);
        $this->assertContains("{$client->address}", $content);
        $this->assertContains("Telephones: {$client->telephones}", $content);
        $this->assertContains("Invoice date: {$invoice->created_at->format('d-m-Y')}", $content);

        // items

        $this->assertContains("Qty", $content);
        $this->assertContains("Type", $content);
        $this->assertContains("Description", $content);
        $this->assertContains("Purchase ID", $content);
        $this->assertContains("Total", $content);

        $this->assertContains("1", $content);
        $this->assertContains("Online shopping", $content);
        $this->assertContains("Buying from amazon", $content);
        $this->assertContains("122452222", $content);
        $this->assertContains("$100.00", $content);

        $this->assertContains("1", $content);
        $this->assertContains("Card commission", $content);
        $this->assertContains("Buying from ebay", $content);
        $this->assertContains("10448796566", $content);
        $this->assertContains("$60.00", $content);

        $this->assertContains("$160.00", $content);
        $this->assertContains("Amount paid", $content);
        $this->assertContains("$80.00", $content);
        $this->assertContains("Pending", $content);
        $this->assertContains("$80.00", $content);

        $this->assertContains("barcode", $content);

        $this->assertContains("{$admin->full_name}", $content);

        $this->assertContains("Terms and Conditions", $content);
        $this->assertContains($tenant->conditionsInvoice->first()->content, $content);
    }
}
