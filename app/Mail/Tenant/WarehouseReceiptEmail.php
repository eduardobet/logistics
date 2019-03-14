<?php

namespace Logistics\Mail\Tenant;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Logistics\DB\Tenant\Tenant;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class WarehouseReceiptEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $tenant;
    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Tenant $tenant, array $data)
    {
        $this->tenant = $tenant;
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $client = $this->data['client'];
        $branch = $client->branch;

        $box = "{$branch->code}{$client->manual_id_dsp}";
        $lang = $this->tenant->lang ? : localization()->getCurrentLocale();
        $ibranch = $this->data['branchTo'];
        $title = " #{$ibranch->initial}-{$this->data['warehouse']->manual_id_dsp}";

        $unique = uniqid('receipt_', true);
        //$this->data['pdf']->save($path = public_path("tenant/{$this->tenant->id}/wh-receipts/{$unique}.pdf"));
        $path = "tenant/{$this->tenant->id}/wh-receipts/{$unique}.pdf";
        Storage::put($path, $this->data['pdf']->output(), 'public');
        //$path = public_path("tenant/{$this->tenant->id}/wh-receipts/receipt_5c8a0fdd67e116.32352438.pdf");


        return $this->subject(__('Warehouse receipt', [], $lang) . $title)
            ->from($this->tenant->mail_from_address, $this->tenant->mail_from_name)
            ->view('tenant.mails.wh-receipt')
            ->attachFromStorage($path, $unique, [
                'as' => $unique,
                'mime' => 'application/pdf',
            ])
            /*->attachRaw($this->data['pdf'], 'name.pdf', [
                'mime' => 'application/pdf',
        ])*/
            ->with(
                array_merge($this->data, [
                    'lang' => $lang,
                ])
            );
    }
}
