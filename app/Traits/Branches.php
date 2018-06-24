<?php

namespace Logistics\Traits;

trait Branches
{
    public function branch($id)
    {
        return $this->branches()->where('id', $id);
    }

    public function branches()
    {
        $tenant = $this->getTenant();

        return cache()->rememberForever("branches.tenant.{$tenant->id}", function () use ($tenant) {
            return $tenant->branches->where('status', 'A');
        });
    }
}
