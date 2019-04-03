<?php

namespace Logistics\Traits;

trait Invoices
{
    public function getInvoice($id)
    {
        return $this->getInvoices()->where('id', $id);
    }

    public function getInvoices()
    {
        $tenant = $this->getTenant();

        return cache()->rememberForever("invoices.tenant.{$tenant->id}", function () use ($tenant) {
            return $tenant->invoices()->with(['details', 'payments'])->get();
        });
    }
}
