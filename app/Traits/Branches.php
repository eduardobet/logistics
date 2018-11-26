<?php

namespace Logistics\Traits;

trait Branches
{
    public function getBranch($id)
    {
        return $this->getBranches()->where('id', $id);
    }

    public function getBranches()
    {
        $tenant = $this->getTenant();

        return cache()->rememberForever("branches.tenant.{$tenant->id}", function () use ($tenant) {
            return $tenant->branches->where('status', 'A');
        });
    }
}
