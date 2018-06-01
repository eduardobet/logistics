<?php

namespace Logistics\Listeners\Tenant;

use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Logistics\Mail\Tenant\WelcomeEmployeeEmail;
use Logistics\Events\Tenant\EmployeeWasCreatedEvent;

class SendEmployeeWelcomeEmail implements ShouldQueue
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
     * @param  object  $event
     * @return void
     */
    public function handle(EmployeeWasCreatedEvent $event)
    {
        Mail::to($event->employee)
            ->send(new WelcomeEmployeeEmail($event->tenant, $event->employee));
    }
}
