<?php

namespace Logistics\Traits;

trait Warehouses
{
    public function getWarehouse($id)
    {
        return $this->getWarehouses()->where('id', $id);
    }

    public function getWarehouses()
    {
        $tenant = $this->getTenant();

        return cache()->rememberForever("warehouses.tenant.{$tenant->id}", function () use ($tenant) {
            return $tenant->warehouses->where('status', 'A');
        });
    }
}
