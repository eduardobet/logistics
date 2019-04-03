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
use Logistics\Mail\Tenant\WarehouseReceiptEmail;

class SendWarehouseReceiptEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Current tenant
     *
     *@var \Logistics\DB\Tenant\Tenant
     */
    public $tenant;

    /**
     * Email data
     *
     *@var Array
     */
    public $data;

    /**
     * Create a new job instance.
     *
     * @param Tenant $tenant
     * @param Array $data
     *
     * @return void
     */
    public function __construct(Tenant $tenant, array $data)
    {
        $this->tenant = $tenant;

        $this->data = $data;
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

        $client = $this->data['client'];
        $extra = $client->extraContacts->where('receive_wh_mail', true);
        $emails = array_merge( [$client->email], $extra->pluck('email')->toArray());

        $mailer->to($emails)
            ->send(new WarehouseReceiptEmail($this->tenant, $this->data));
    }
}
