<?php

namespace Logistics\Http\Controllers\Tenant\Auth;

use Logistics\DB\User;
use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
use Illuminate\Support\Facades\Validator;
use Logistics\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AccountActivationController extends Controller
{
    use Tenant;

    public function showUnlockForm($domain, $email, $token)
    {
        $tenant = $this->getTenant();

        $employee = $tenant->employees()
            ->whereEmail($email)
            ->whereToken($token)
            ->whereStatus('L')
            ->first();

        if (!$employee) {
            return redirect()->route('tenant.auth.get.login', $tenant->domain)->with('flash_error', __('We could not find a user associated with this email!'));
        }

        return view('tenant.auth.unlock', compact('employee'));
    }

    public function unlock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|alpha_num_pwd',
        ]);

        if ($validator->fails()) {
            return redirect()->route('tenant.auth.get.login', $request->domain)
                ->withErrors($validator)
                ->withInput();
        }

        $tenant = $this->getTenant();

        $employee = $tenant->employees()
            ->whereEmail($request->email)
            ->whereToken($request->token)
            ->whereStatus('L')
            ->first();

        if (!$employee) {
            return redirect()->route('tenant.auth.get.login', $request->domain)->with('flash_error', __('We could not find a user associated with this email!'));
        }

        $employee->password = bcrypt($request->password);
        $employee->status = 'A';
        $employee->token = null;
        $updated = $employee->update();

        $route = 'tenant.admin.dashboard';

        if (!$employee->isAdmin()) {
            $route = 'tenant.employee.dashboard';
        }

        if ($updated) {
            auth()->login($employee);

            return redirect()->route($route, $request->domain);
        }

        return redirect()->back()->withErrors(__('Sorry! we could not unlock your account. Please contact the administrator.'));
    }
}
