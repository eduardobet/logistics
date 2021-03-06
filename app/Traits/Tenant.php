<?php

namespace Logistics\Traits;

use Logistics\DB\Tenant\Tenant as Model;

trait Tenant
{
    use Positions, Permissions, Branches, Mailers, Clients, Invoices, Warehouses;

    /**
     * Get the actual tenant based on the url
     * @return \Logistics\DB\Tenant\Tenant
     */
    protected function getTenant($domain = null)
    {
        if ($domain) {
            $host = $domain;
        } else {
            $host = get_host();
        }

        $tenant = cache()->rememberForever("$host", function () use ($host) {
            return Model::whereDomain($host)->first();
        });

        return $tenant;
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
