<?php

namespace Logistics\Events\Tenant;

use Logistics\DB\Tenant\Client;
use Logistics\DB\Tenant\Tenant;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class ClientWasCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tenant;
    
    public $client;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Tenant $tenant, Client $client)
    {
        $this->tenant = $tenant;

        $this->client = $client;
    }
}
