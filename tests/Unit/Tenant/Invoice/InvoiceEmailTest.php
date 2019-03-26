<?php

namespace Tests\Unit\Tenant\Warehouse;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Box;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Client;
use Illuminate\Support\Facades\Mail;
use Logistics\Mail\Tenant\InvoiceCreated;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Logistics\Jobs\Tenant\SendInvoiceCreatedEmail;

class InvoiceEmailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function email_has_the_correct_data()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create(['lang' => 'en']);
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id, ]);

        $this->actingAs($admin);

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'pay_volume' => true, 'vol_price' => 2.00, 'manual_id' => 1, ]);

        $box = factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branchB->id,
            'branch_code' => $branchB->code,
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

        $email = new InvoiceCreated($tenant, $invoice);

        $data = $email->buildViewData();
        $content = $this->render($email);

        $this->assertTrue($data['invoice']->is($invoice));

        $this->assertEquals("Invoice #{$branchB->initial}-{$invoice->manual_id_dsp}", $email->build()->subject);

        $this->assertContains("#{$branchB->initial}-{$invoice->manual_id_dsp}", $content);
        $this->assertContains("{$branch->name}", $content);
        $this->assertContains("{$branch->ruc} DV {$branch->dv}", $content);
        $this->assertContains("{$branch->address}", $content);
        $this->assertContains("{$branch->telephones}", $content);
        $this->assertContains("{$client->full_name} / {$box->branch_code}{$client->manual_id_dsp}", $content);
        $this->assertContains("{$client->address}", $content);
        $this->assertContains("Telephones: {$client->telephones}", $content);
        $this->assertContains("Invoice date: {$invoice->created_at->format('d-m-Y')}", $content);

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

        $this->assertContains("{$admin->full_name}", $content);
    }

    /** @test */
    public function it_sends_the_invoice_email_to_the_client()
    {
        $this->withoutExceptionHandling();

        Mail::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, ]);

        factory(Box::class)->create([
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

        $invoice->details()->create([
            'qty' => 1,
            'type' => 1,
            'description' => 'Buying from amazon',
            'id_remote_store' => '122452222',
            'total' => 160,
        ]);

        dispatch(new SendInvoiceCreatedEmail($tenant, $invoice));

        Mail::assertSent(InvoiceCreated::class, function ($mail) use ($tenant, $client) {
            return $mail->hasTo($client->email)
                && $mail->tenant->is($tenant);
        });
    }

    /** @test */
    public function it_sends_the_invoice_email_to_the_client_extra_contact()
    {
        $this->withoutExceptionHandling();

        Mail::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, ]);

        $extraContact = $client->extraContacts()->create([
            'full_name' => 'Extra Contact',
            'pid' => '1253-587',
            'email' => 'extra-contact@email.test',
            'telephones' => '555-5555',
            'tenant_id' => $tenant->id,
            'receive_inv_mail' => true,
            'receive_wh_mail' => true,
        ]);

        factory(Box::class)->create([
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

        $invoice->details()->create([
            'qty' => 1,
            'type' => 1,
            'description' => 'Buying from amazon',
            'id_remote_store' => '122452222',
            'total' => 160,
        ]);

        dispatch(new SendInvoiceCreatedEmail($tenant, $invoice));

        Mail::assertSent(InvoiceCreated::class, function ($mail) use ($extraContact, $client) {
            return $mail->hasTo($client->email) && $mail->hasTo( $extraContact->email);
        });
    }
}
