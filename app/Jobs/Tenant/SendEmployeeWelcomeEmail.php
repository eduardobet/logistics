<?php

namespace Logistics\Jobs\Tenant;

use Logistics\DB\User;
use Illuminate\Bus\Queueable;
use Logistics\DB\Tenant\Tenant;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Logistics\Mail\Tenant\WelcomeEmployeeEmail;

class SendEmployeeWelcomeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Current tenant
     *
     *@var \Logistics\DB\Tenant\Tenant
     */
    public $tenant;

    /**
     * Current employee
     *
     *@var \Logistics\DB\User
     */
    public $employee;

    /**
     * Create a new job instance.
     *
     * @param Tenant $tenant
     * @param User $employee
     *
     * @return void
     */
    public function __construct(Tenant $tenant, User $employee)
    {
        $this->tenant = $tenant;

        $this->employee = $employee;
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

        $mailer->to($this->employee)
            ->send(new WelcomeEmployeeEmail($this->tenant, $this->employee));
    }
}
