<?php

namespace Logistics\Events\Tenant;

use Logistics\DB\Tenant\Tenant;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class CompanyLogoAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tenant;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }
}
