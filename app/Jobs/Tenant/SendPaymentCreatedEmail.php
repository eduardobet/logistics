<?php

namespace Logistics\Jobs\Tenant;

use Illuminate\Bus\Queueable;
use Logistics\DB\Tenant\Client;
use Logistics\DB\Tenant\Tenant;
use Logistics\DB\Tenant\Invoice;
use Logistics\DB\Tenant\Payment;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Logistics\Mail\Tenant\PaymentCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendPaymentCreatedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Current tenant
     *
     *@var Logistics\DB\Tenant\Tenant
     */
    public $tenant;

    /**
     * Current client
     *
     *@var Logistics\DB\Tenant\Client
     */
    public $client;

    /**
     * Current invoice
     *
     *@var Logistics\DB\Tenant\Invoice
     */
    public $invoice;

    /**
     * Current payment
     *
     *@var Logistics\DB\Tenant\Payment
     */
    public $payment;

    /**
     * Create a new job instance.
     *
     * @param Tenant $tenant
     * @param Client $client
     * @param Invoice $invoice
     * @param Payment $payment
     *
     * @return void
     */
    public function __construct(Tenant $tenant, Client $client, Invoice $invoice, Payment $payment)
    {
        $this->tenant = $tenant;
        $this->client = $client;
        $this->invoice = $invoice;
        $this->payment = $payment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Mailer $mailer)
    {
        $security = $this->tenant->mail_encryption ? : '';

        $transport = (new \Swift_SmtpTransport($this->tenant->mail_host, $this->tenant->mail_port, $security))
            ->setUsername($this->tenant->mail_username)
            ->setPassword($this->tenant->mail_password);

        if (!app()->environment('testing')) {
            $mailer->setSwiftMailer(new \Swift_Mailer($transport));
        }

        $mailer->to($this->invoice->client->email)
            ->send(new PaymentCreated($this->tenant, $this->client, $this->invoice, $this->payment));
    }
}
