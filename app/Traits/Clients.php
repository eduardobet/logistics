<?php

namespace Logistics\Traits;

trait Clients
{
    public function client($id)
    {
        return $this->client()->where('id', $id);
    }

    public function clients()
    {
        $tenant = $this->getTenant();

        return cache()->rememberForever("clients.tenant.{$tenant->id}", function () use ($tenant) {
            return $tenant->clients()->with('boxes')->where('status', 'A')->get();
        });
    }
}
