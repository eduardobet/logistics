<?php

namespace Logistics\Mail\Tenant;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Logistics\DB\Tenant\Tenant;
use Logistics\DB\Tenant\Invoice;
use Illuminate\Queue\SerializesModels;

class InvoiceCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $tenant;
    public $invoice;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Tenant $tenant, Invoice $invoice)
    {
        $this->tenant = $tenant;
        $this->invoice = $invoice;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $client = $this->invoice->client;
        $branch = $client->branch;

        $box = "{$branch->code}{$client->manual_id}";
        $lang = $this->tenant->lang ? : localization()->getCurrentLocale();
        $ibranch = $this->invoice->branch;
        $title = " #{$ibranch->initial}-{$this->invoice->id}";

        return $this->subject(__('Invoice', [], $lang) . $title)
            ->from($this->tenant->mail_from_address, $this->tenant->mail_from_name)
            ->markdown('tenant.mails.invoice')
            ->with([
                'tenant' => $this->tenant,
                'ibranch' => $ibranch,
                'client' => $client,
                'box' => $box,
                'payments' => $this->invoice->payments,
                'creatorName' => $this->invoice->creator ? $this->invoice->creator->full_name : null,
                'lang' => $lang,
            ]);
    }
}
