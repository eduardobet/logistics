<?php

namespace Logistics\Listeners\Tenant;

use Logistics\Events\Tenant\EmployeeAvatarAdded;
use Logistics\Jobs\Tenant\ProcessAvatar;

class ScheduleEmployeeLogoProcessing
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
     * @param  EmployeeAvatarAdded  $event
     * @return void
     */
    public function handle(EmployeeAvatarAdded $event)
    {
        ProcessAvatar::dispatch($event->employee);
    }
}
