<?php

namespace Logistics\Http\Controllers\Tenant\Auth;

use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
use Logistics\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;

class ResetPasswordController extends Controller
{
    use ResetsPasswords, Tenant;

    public function showResetForm(Request $request, $token = null)
    {
        return view('tenant.auth.reset')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|alpha_num_pwd|confirmed',
        ];
    }

    /**
     * Get the response for a successful password reset.
     *
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetResponse($response)
    {
        return $this->tenantRedirect($response);
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @param  \Illuminate\Http\Request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return redirect()->route('tenant.user.password.reset')->withErrors(
            ['email' => trans($response)]
        );
    }

    /**
     * Redirect to employee or admin dashboard and home if error
     *
     * @return Response
     */
    private function tenantRedirect($response)
    {
        $type = auth()->user()->type;
        $tenant = $this->getTenant();

        if (!$tenant) {
            return redirect()->route('tenant.home')->with('flash_error', __('Invalid Tenant.'));
        }

        if ($tenant->hasActiveSubscription()) {
            $prefix = 'employee';

            if ($type == 'A') {
                $prefix = 'admin';
            }

            $url = localization()->getLocalizedURL(
                auth()->user()->lang ? : localization()->getCurrentLocale(),
                redirect()->intended(route("tenant.{$prefix}.dashboard"))->getTargetUrl()
            );

            return redirect($url);
        } else {
        }

        auth()->logout();

        return redirect()->route('tenant.home')->with('flash_error', __('Invalid user type.'));
    }
}
