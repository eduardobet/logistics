<?php

namespace Logistics\Listeners\Tenant;

use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Logistics\Mail\Tenant\WelcomeClientEmail;
use Logistics\Events\Tenant\ClientWasCreatedEvent;

class SendClientWelcomeEmail implements ShouldQueue
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
     * @param  ClientWasCreatedEvent  $event
     * @return void
     */
    public function handle(ClientWasCreatedEvent $event)
    {
        Mail::to($event->client)
            ->send(new WelcomeClientEmail($event->tenant, $event->client));
    }
}
