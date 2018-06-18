<?php

namespace Logistics\Http\Controllers\Tenant\Auth;

use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
use Illuminate\Support\Facades\Validator;
use Logistics\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use Tenant, ThrottlesLogins;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('tenant.auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'   => 'required|email',
            'password' => 'required|alpha_num_pwd',
        ]);

        if ($validator->fails()) {
            return redirect()->route('tenant.auth.get.login')
              ->withErrors($validator)
              ->withInput($request->only('email', 'remember'));
        }

        
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $loginData = [
            'email' => $request->email,
            'password' => $request->password,
            'status' => 'A',
            'tenant_id' => $this->getTenantId(),
        ];

        if (auth()->attempt($loginData, $request->remember)) {
            return $this->doRedirect($request);
        }

        $this->incrementLoginAttempts($request);

        return redirect()->route('tenant.auth.get.login')
                ->with('flash_error', __("Invalid Email and/or password or you've haven't unlocked your account yet."))
                ->withErrors($validator)
                ->withInput($request->only('email', 'remember'));
    }

    protected function doRedirect($request)
    {
        $type = $request->user()->type;
        $tenant = $this->getTenant();

        $this->clearLoginAttempts($request);

        if (!$tenant) {
            return redirect()->route('app.home')->with('flash_error', __('Invalid Client.'));
        }

        if ($tenant->hasActiveSubscription()) {
            $prefix = 'employee';

            if ($type == 'A') {
                $prefix = 'admin';
            }
            $url = localization()->getLocalizedURL(
                $tenant->lang ?: localization()->getCurrentLocale(),
                redirect()->intended(route("tenant.{$prefix}.dashboard"))->getTargetUrl()
            );

            return redirect($url);
        } else {
            if ($request->user()->is_main_admin) {
                // return redirect()->route('client.registration.edit', $request->client);
            }
        }

        auth()->logout();

        return redirect()->route('app.home')->with('flash_error', __('Invalid user type.'));
    }

    public function logout()
    {
        auth()->logout();

        return redirect()->route('tenant.auth.get.login');
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'email';
    }

    /**
     * Redirect the user after determining they are locked out.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendLockoutResponse(Request $request)
    {
        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

        $message = __('auth.throttle', ['seconds' => $seconds]);

        $errors = ['email' => $message];

        if ($request->expectsJson()) {
            return response()->json($errors, 423);
        }

        return redirect()->route('tenant.auth.get.login')
            ->withErrors($errors)->with('flash_lock_error', $message);
    }
}
