<?php

namespace Logistics\Events\Tenant;

use Logistics\DB\Tenant\Branch;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class BranchLogoAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $branch;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Branch $branch)
    {
        $this->branch = $branch;
    }
}
