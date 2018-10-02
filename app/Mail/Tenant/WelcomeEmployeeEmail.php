<?php

namespace Logistics\Mail\Tenant;

use Logistics\DB\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Logistics\DB\Tenant\Tenant;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class WelcomeEmployeeEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Current tenant
     *
     *@var Logistics\DB\Tenant\Tenant
     */
    public $tenant;

    /**
     * The newly created employee
     *
     *@var Logistics\DB\User
     */
    public $employee;

    /**
     * Create a new message instance.
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
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(__('Welcome') . ' ' . $this->employee->full_name)
            ->from($this->tenant->mail_from_address, $this->tenant->mail_from_name)
            ->markdown('tenant.mails.welcome-employee')
            ->with([
                'tenant' => $this->tenant,
                'employee' => $this->employee,
                'lang' => $this->tenant->lang ? : localization()->getCurrentLocale(),
            ]);
    }
}
