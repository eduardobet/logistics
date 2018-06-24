<?php

namespace Logistics\Traits;

trait Mailers
{
    public function mailer($id)
    {
        return $this->mailers()->where('id', $id);
    }

    public function mailers()
    {
        $tenant = $this->getTenant();

        return cache()->rememberForever("mailers.tenant.{$tenant->id}", function () use ($tenant) {
            return $tenant->mailers->where('status', 'A');
        });
    }
}
