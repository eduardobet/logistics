<?php

namespace Logistics\Traits;

trait Invoices
{
    public function invoice($id)
    {
        return $this->invoice()->where('id', $id);
    }

    public function invoices()
    {
        $tenant = $this->getTenant();

        return cache()->rememberForever("invoices.tenant.{$tenant->id}", function () use ($tenant) {
            return $tenant->invoices()->with('details')->get();
        });
    }
}
