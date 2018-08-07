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
            abort(404);
        } elseif (isset($tenant) && $tenant->status != 'A') {
            auth()->logout();

            return redirect()
                ->route('app.home')
                ->with('flash_inactive_tenant', __('This client is inactive. Please contact your administrator!'));
        } else {
            view()->share([
                'tenant' => $tenant,
            ]);
            
            if (auth()->check()) {
                if (auth()->user()->tenant_id != $tenant->id) {
                    return redirect()
                        ->route('app.home')
                        ->withErrors([__('You cannot access this site!')]);
                } else {
                    view()->share([
                        'user' => auth()->user(),
                        'branch' => auth()->user()->currentBranch()
                    ]);
                }
            }
        }
        
        return $next($request);
    }
}
