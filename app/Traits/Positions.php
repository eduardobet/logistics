<?php

namespace Logistics\Traits;

trait Positions
{
    public function position($id)
    {
        return $this->positions()->where('id', $id);
    }

    public function positions()
    {
        $tenant = $this->getTenant();

        return cache()->rememberForever("positions.tenant.{$tenant->id}", function () use ($tenant) {
            return $tenant->positions->where('status', 'A');
        });
    }
}
