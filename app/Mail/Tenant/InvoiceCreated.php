<?php

namespace Logistics\Mail\Tenant;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Logistics\DB\Tenant\Invoice;
use Logistics\DB\Tenant\Payment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class InvoiceCreated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $invoice;
    public $paymentId;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Invoice $invoice, $paymentId)
    {
        $this->invoice = $invoice;
        $this->paymentId = $paymentId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $client = $this->invoice->client;
        $box = $client->boxes()->active()->get()->first();
        $box = "{$box->branch_code}{$client->id}";
        
        return $this->subject(__('Invoice') . ' #' . $this->invoice->id)
            ->markdown('tenant.mails.invoice')
            ->with([
                'tenant' => $this->invoice->tenant,
                'ibranch' => $this->invoice->branch,
                'client' => $client,
                'box' => $box,
                'payments' => $this->invoice->payments,
                'creatorName' => $this->invoice->creator->full_name
            ]);
    }
}
