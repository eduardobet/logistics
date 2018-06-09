<?php

namespace Logistics\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \Logistics\Events\Tenant\EmployeeWasCreatedEvent::class => [
            \Logistics\Listeners\Tenant\SendEmployeeWelcomeEmail::class,
        ],
        
        \Logistics\Events\Tenant\EmployeeAvatarAdded::class => [
            \Logistics\Listeners\Tenant\ScheduleEmployeeLogoProcessing::class,
        ],

        \Logistics\Events\Tenant\CompanyLogoAdded::class => [
            \Logistics\Listeners\Tenant\ScheduleCompanyLogoProcessing::class,
        ],

        \Logistics\Events\Tenant\ClientWasCreatedEvent::class => [
            \Logistics\Listeners\Tenant\SendClientWelcomeEmail::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
