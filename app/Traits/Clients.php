<?php

namespace Logistics\Traits;

trait Clients
{
    public function getClient($id)
    {
        return $this->client()->where('id', $id);
    }

    public function getClients()
    {
        $tenant = $this->getTenant();

        return cache()->rememberForever("clients.tenant.{$tenant->id}", function () use ($tenant) {
            return $tenant->clients()->with('branch')->where('status', 'A')->get();
        });
    }
}
