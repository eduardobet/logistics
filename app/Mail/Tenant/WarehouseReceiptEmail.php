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

        $unique = "whreceipt-{$ibranch->initial}-{$this->data['warehouse']->manual_id_dsp}";
        $path = "/tenant/{$this->tenant->id}/{$unique}.pdf";

        $pdf = \PDF::loadView('tenant.warehouse.receipt', array_merge($this->data, [
            'tenant' => $this->tenant,
        ]));

        Storage::disk('whreceipts')->put($path, $pdf->output());

        return $this->subject(__('Warehouse receipt', [], $lang) . $title)
            ->from($this->tenant->mail_from_address, $this->tenant->mail_from_name)
            ->view('tenant.mails.wh-receipt')
            ->attach(public_path('whreceipts' . $path), [
                'as' => $unique,
                'mime' => 'application/pdf',
            ])
            ->with(
                array_merge($this->data, [
                    'box' => $box,
                    'lang' => $lang,
                    'path' => asset('whreceipts' . $path),
                ])
            );
    }
}
