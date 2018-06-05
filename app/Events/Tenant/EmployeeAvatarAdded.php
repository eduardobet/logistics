<?php

namespace Logistics\Events\Tenant;

use Logistics\DB\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class EmployeeAvatarAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $employee;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $employee)
    {
        $this->employee = $employee;
    }
}
