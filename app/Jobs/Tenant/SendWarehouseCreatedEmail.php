<?php

namespace Logistics\Jobs\Tenant;

use Illuminate\Bus\Queueable;
use Logistics\DB\Tenant\Tenant;
use Logistics\DB\Tenant\Warehouse;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Logistics\Mail\Tenant\WarehouseCreatedEmail;

class SendWarehouseCreatedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Current tenant
     *
     *@var \Logistics\DB\Tenant\Tenant
     */
    public $tenant;

    /**
     * Current warehouse
     *
     *@var \Logistics\DB\Tenant\Warehouse
     */
    public $warehouse;

    /**
     * Create a new job instance.
     *
     * @param Tenant $tenant
     * @param Warehouse $warehouse
     *
     * @return void
     */
    public function __construct(Tenant $tenant, Warehouse $warehouse)
    {
        $this->tenant = $tenant;

        $this->warehouse = $warehouse;
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

        $client = $this->warehouse->client;
        $extra = $client->extraContacts->where('receive_inv_mail', true);

        $emails = array_merge( [$client->email], $extra->pluck('email')->toArray());

        $mailer->to($emails)
            ->send(new WarehouseCreatedEmail($this->tenant, $this->warehouse));
    }
}
