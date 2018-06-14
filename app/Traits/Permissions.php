<?php

namespace Logistics\Traits;

trait Permissions
{
    public function permission($id)
    {
        return $this->permissions()->where('id', $id);
    }

    public function permissions()
    {
        $tenant = $this->getTenant();

        return cache()->rememberForever("permissions.tenant.{$tenant->id}", function () use ($tenant) {
            return $tenant->permissions->where('status', 'A');
        });
    }
}
