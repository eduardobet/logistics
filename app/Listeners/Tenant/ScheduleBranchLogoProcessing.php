<?php

namespace Logistics\Listeners\Tenant;

use Logistics\Events\Tenant\BranchLogoAdded;
use Logistics\Jobs\Tenant\ProcessBranchLogo;

class ScheduleBranchLogoProcessing
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  BranchLogoAdded  $event
     * @return void
     */
    public function handle(BranchLogoAdded $event)
    {
        ProcessBranchLogo::dispatch($event->branch);
    }
}
