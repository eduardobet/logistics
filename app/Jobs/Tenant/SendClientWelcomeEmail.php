<?php

namespace Logistics\Jobs\Tenant;

use Illuminate\Bus\Queueable;
use Logistics\DB\Tenant\Client;
use Logistics\DB\Tenant\Tenant;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendClientWelcomeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
     * Create a new job instance.
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

        $mailer->setSwiftMailer(new \Swift_Mailer($transport));

        $mailer->to($this->client)
            ->send(new \Logistics\Mail\Tenant\WelcomeClientEmail($this->tenant, $this->client));
    }
}
