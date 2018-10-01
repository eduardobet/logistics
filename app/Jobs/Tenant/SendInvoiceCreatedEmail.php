<?php

namespace Logistics\Jobs\Tenant;

use Illuminate\Bus\Queueable;
use Logistics\DB\Tenant\Tenant;
use Logistics\DB\Tenant\Invoice;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Logistics\Mail\Tenant\InvoiceCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendInvoiceCreatedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Current tenant
     *
     *@var Logistics\DB\Tenant\Tenant
     */
    public $tenant;

    /**
     * Current invoice
     *
     *@var Logistics\DB\Tenant\Invoice
     */
    public $invoice;

    /**
     * Create a new job instance.
     *
     * @param Tenant $tenant
     * @param Invoice $invoice
     *
     * @return void
     */
    public function __construct(Tenant $tenant, Invoice $invoice)
    {
        $this->tenant = $tenant;

        $this->invoice = $invoice;
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
            ->send(new InvoiceCreated($this->tenant, $this->invoice));
    }
}
