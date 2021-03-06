<?php

namespace Logistics\Mail\Tenant;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Logistics\DB\Tenant\Client;
use Logistics\DB\Tenant\Tenant;
use Logistics\DB\Tenant\Invoice;
use Logistics\DB\Tenant\Payment;
use Illuminate\Queue\SerializesModels;

class PaymentCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $tenant;
    public $client;
    public $invoice;
    public $payment;

    /**
     * Create a new message instance.
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
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(__('Payment', [], $this->tenant->lang) . ' #' . $this->payment->id)
            ->from($this->tenant->mail_from_address, $this->tenant->mail_from_name)
            ->markdown('tenant.mails.payment')
            ->with([
                'lang' => $this->tenant->lang,
                'ibranch' => $this->invoice->branch,
            ]);
    }
}
