<?php

namespace Logistics\Events\Tenant;

use Logistics\DB\User;
use Logistics\DB\Tenant\Tenant;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class EmployeeWasCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The newly created tenant
     *
     *@var Logistics\DB\Tenant\Tenant
     */
    public $tenant;

    /**
     * The newly created Employee
     *
     *@var Logistics\DB\Tenant\User
     */
    public $employee;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Tenant $tenant, User $employee)
    {
        $this->tenant = $tenant;
        
        $this->employee = $employee;
    }
}
