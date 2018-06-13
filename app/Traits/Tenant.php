<?php

namespace Logistics\Traits;

use Logistics\DB\Tenant\Tenant as Model;

trait Tenant
{
    use Positions;

    /**
     * Get the actual tenant based on the url
     * @return Eloquent
     */
    protected function getTenant()
    {
        $host = get_host();

        return cache()->rememberForever("$host", function () use ($host) {
            return Model::whereDomain($host)->first();
        });
    }

    /**
     * Get the actual tenant based on the url
     * @return int
     */
    protected function getTenantId()
    {
        return $this->getTenant()->id;
    }
}
