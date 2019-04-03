<?php

namespace Logistics\Mail\Tenant;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Logistics\DB\Tenant\Tenant;
use Logistics\DB\Tenant\Warehouse;
use Illuminate\Queue\SerializesModels;

class WarehouseCreatedEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The tenant
     *
     * @var Logistics\DB\Tenant\Tenant
     */
    public $tenant;

    /**
     * The warehouse
     *
     * @var Logistics\DB\Tenant\Warehouse
     */
    public $warehouse;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Tenant $tenant, Warehouse $warehouse)
    {
        $this->tenant = $tenant;

        $this->warehouse = $warehouse;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $client = $this->warehouse->client;
        $branch = $client->branch;

        $box = "{$branch->code}{$client->manual_id_dsp}";
        $lang = $this->tenant->lang ? : localization()->getCurrentLocale();
        $ibranch = $this->warehouse->toBranch;
        $title = " #{$ibranch->initial}-{$this->warehouse->manual_id_dsp}";

        return $this->subject(__('Warehouse', [], $lang) . $title)
            ->from($this->tenant->mail_from_address, $this->tenant->mail_from_name)
            ->markdown('tenant.mails.warehouse')
            ->with([
                'tenant' => $this->tenant,
                'ibranch' => $ibranch,
                'client' => $client,
                'box' => $box,
                'lang' => $lang,
            ]);
    }
}
