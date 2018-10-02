<?php

namespace Logistics\Mail\Tenant;

use Logistics\DB\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Logistics\DB\Tenant\Client;
use Logistics\DB\Tenant\Tenant;
use Illuminate\Queue\SerializesModels;
use Logistics\Mail\ConfigurableMailable;

class WelcomeClientEmail extends Mailable
{
    use Queueable, SerializesModels;

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
     * Create a new message instance.
     *
     * @param Client $client
     *
     * @return void
     */
    public function __construct(Tenant $tenant, Client $client)
    {
        $this->tenant = $tenant;

        $this->client = $client;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $box = $this->client->boxes()->active()->get()->first();
        $branch = $box->branch;
        $addresses = $this->tenant->remoteAddresses;
        $air = $addresses->where('type', 'A')->first();
        $maritime = $addresses->where('type', 'M')->first();
        $lang = $this->tenant->lang ? : localization()->getCurrentLocale();

        return $this->subject(__('Welcome', [], $lang) . ' ' . $this->client->full_name)
            ->from($this->tenant->mail_from_address, $this->tenant->mail_from_name)
            ->markdown('tenant.mails.welcome-client')
            ->with([
                'tenant' => $this->tenant,
                'client' => $this->client,
                'box_code' => $box->branch_code,
                'air' => $air,
                'maritime' => $maritime,
                'branch' => $branch,
                'lang' => $lang,
                'subcopy' => $branch->address . '<br>' . $branch->telephones
            ]);
    }
}
