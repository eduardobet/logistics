<?php

namespace Logistics\Http\Middleware\Tenant;

use Closure;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->user()->type == 'A') {
            return $next($request);
        }

        auth()->logout();
        
        return redirect()
            ->route('tenant.auth.get.login', $request->domain)->with('flash_error', __('This is an admin only module.'));
    }
}
