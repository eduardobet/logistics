<?php

namespace Logistics\Providers;

use Logistics\Traits\Tenant;
use Illuminate\Support\Facades\Gate;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    use Tenant;

    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(GateContract $gate)
    {
        $this->registerPolicies();

        if (!app()->environment('testing')) {
            foreach ($this->permissions() as $permission) {
                $gate->define($permission->slug, function ($user) use ($permission) {
                    return in_array($permission->slug, $user->permissions);
                });
            }
        }
    }
}
