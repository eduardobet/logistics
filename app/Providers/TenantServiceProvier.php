<?php

namespace Logistics\Providers;

use Logistics\Traits\Tenant;
use Illuminate\Support\ServiceProvider;

class TenantServiceProvier extends ServiceProvider
{
    use Tenant;
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if (!app()->environment('testing')) {
            $tenant = $this->getTenant();

            if ($tenant) {
                $tenant->setConfigs();
            }
        }
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
