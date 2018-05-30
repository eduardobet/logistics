<?php

namespace Logistics\Http\Middleware;

use Closure;
use Logistics\Traits\Tenant as TenantModel;

class Tenant
{
    use TenantModel;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $tenant = $this->getTenant();

        if (empty($tenant)) {
            return redirect()->route('app.home');
        } elseif (isset($tenant) && $tenant->status != 'A') {
            auth()->logout();

            return redirect()->route('app.home')->with('flash_inactive_tenant', __('This client is inactive. Please contact your administrator!'));
        } else {
            view()->share([
                'tenant' => $tenant,
            ]);
        }

        if (auth()->check()) {
            if (auth()->user()->tenant_id != $tenant->id) {
                auth()->logout();

                if ($request->routeIs('tenant.home')) {
                    return redirect()->route('tenant.home');
                }

                return redirect()->guest('auth/login')->withErrors([__('You cannot access this site!')]);
            } else {
                view()->share('user', auth()->user());
            }
        }

        return $next($request);
    }
}
