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
        $id = $tenant ? $tenant->id : 0;

        return cache()->rememberForever("permissions.tenant.{$id}", function () use ($tenant) {
            return !$tenant ? collect([]) : $tenant->permissions->where('status', 'A');
        });
    }
}
