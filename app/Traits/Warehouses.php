<?php

namespace Logistics\Traits;

trait Warehouses
{
    public function warehouse($id)
    {
        return $this->warehouse()->where('id', $id);
    }

    public function warehouses()
    {
        $tenant = $this->getTenant();

        return cache()->rememberForever("warehouses.tenant.{$tenant->id}", function () use ($tenant) {
            return $tenant->warehouses->where('status', 'A');
        });
    }
}
