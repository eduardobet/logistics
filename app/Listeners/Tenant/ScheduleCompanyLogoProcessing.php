<?php

namespace Logistics\Listeners\Tenant;

use Logistics\Jobs\Tenant\ProcessLogo;
use Logistics\Events\Tenant\CompanyLogoAdded;

class ScheduleCompanyLogoProcessing
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
     * @param  CompanyLogoAdded  $event
     * @return void
     */
    public function handle(CompanyLogoAdded $event)
    {
        ProcessLogo::dispatch($event->tenant);
    }
}
