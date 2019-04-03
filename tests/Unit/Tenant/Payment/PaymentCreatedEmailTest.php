<?php

namespace Tests\Unit\Tenant\Payment;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Box;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Client;
use Illuminate\Support\Facades\Mail;
use Logistics\Mail\Tenant\PaymentCreated;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Logistics\Jobs\Tenant\SendPaymentCreatedEmail;

class PaymentCreatedEmailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function email_has_the_correct_data()
    {
        $tenant = factory(TenantModel::class)->create(['lang' => 'en']);
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id,]);

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'pay_volume' => true, 'vol_price' => 2.00]);
        factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);

        $invoice = $tenant->invoices()->create([
            'branch_id' => $branch->id,
            'client_id' => $client->id,
            'total' => 100,
        ]);

        $invoice->details()->create([
            'qty' => 1,
            'type' => 1,
            'description' => 'Buying from amazon',
            'id_remote_store' => '122452222',
            'total' => 100,
        ]);

        $payment = $invoice->payments()->create([
            'tenant_id' => $invoice->tenant_id,
            'amount_paid' => 50,
            'payment_method' => 1,
            'payment_ref' => 'The client paid $50.00',
            'is_first' => true,
        ]);

        $email = new PaymentCreated($tenant, $client, $invoice, $payment);
        $data = $email->buildViewData();
        $content = $this->render($email);

        $this->assertTrue($data['tenant']->is($tenant));
        $this->assertTrue($data['client']->is($client));
        $this->assertTrue($data['invoice']->is($invoice));
        $this->assertTrue($data['payment']->is($payment));

        $this->assertContains("{$branch->name}", $content);
        $this->assertContains( "TAX ID {$branch->ruc} DV {$branch->dv}", $content);
        $this->assertContains("{$branch->address}", $content);
        $this->assertContains("{$branch->telephones}", $content);

        $this->assertContains("Invoice No.", $content);
        $this->assertContains("{$branch->initial}-{$invoice->manual_id_dsp}", $content);
        $this->assertContains("Payment No.", $content);
        $this->assertContains("{$payment->id}", $content);
        $this->assertContains("Payment date", $content);
        $this->assertContains("{$payment->created_at->format('d-m-Y')}", $content);
        $this->assertContains("Client", $content);
        $this->assertContains("{$client->full_name} / {$client->pid} / {$client->telephones}", $content);

        $this->assertContains("Amount", $content);
        $this->assertContains(number_format($payment->amount_paid, 2), $content);
        $this->assertContains( "Payment method", $content);
        $this->assertContains("Cash", $content);
        $this->assertContains( "Concept", $content);
        $this->assertContains($payment->payment_ref, $content);
    }

    /** @test */
    public function it_sends_the_payment_email_to_the_client()
    {
        $this->withoutExceptionHandling();

        Mail::fake();

        $tenant = factory(TenantModel::class)->create(['lang' => 'en']);
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id,]);

         $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'pay_volume' => true, 'vol_price' => 2.00]);
        factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);

        $invoice = $tenant->invoices()->create([
            'branch_id' => $branch->id,
            'client_id' => $client->id,
            'total' => 100,
        ]);

        $invoice->details()->create([
            'qty' => 1,
            'type' => 1,
            'description' => 'Buying from amazon',
            'id_remote_store' => '122452222',
            'total' => 100,
        ]);

        $payment = $invoice->payments()->create([
            'tenant_id' => $invoice->tenant_id,
            'amount_paid' => 50,
            'payment_method' => 1,
            'payment_ref' => 'The client paid $50.00',
            'is_first' => true,
        ]);

        dispatch(new SendPaymentCreatedEmail($tenant, $invoice, $payment));

        Mail::assertSent( PaymentCreated::class, function ($mail) use ($tenant, $client, $invoice, $payment) {
            return $mail->hasTo($client->email)
                && $mail->tenant->is($tenant)
                && $mail->client->is($client)
                && $mail->invoice->is($invoice)
                && $mail->payment->is($payment)
                ;
        });
    }
}
