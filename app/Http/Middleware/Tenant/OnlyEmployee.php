<?php

namespace Logistics\Http\Middleware\Tenant;

use Closure;

class OnlyEmployee
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
        $user = $request->user();

        if ($user->isAdmin() || $user->isEmployee()) {
            return $next($request);
        }

        auth()->logout();
        
        abort(401, __('Unauthorized'));
    }
}
