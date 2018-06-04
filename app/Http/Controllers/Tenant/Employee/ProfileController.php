<?php

namespace Logistics\Http\Controllers\Tenant\Employee;

use Illuminate\Http\Request;
use Logistics\Http\Controllers\Controller;
use Logistics\Http\Requests\Tenant\ProfileRequest;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('tenant.employee.profile');
    }

    public function update(ProfileRequest $request)
    {
        $updated = auth()->user()->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
        ]);


        if ($updated) {
            $this->uploadAvatar($request->avatar);

            return redirect()->route('tenant.employee.profile.edit')
                ->with('flash_success', __('Your profile has been edited.'));
        }

        return redirect()->back()
            ->with('flash_error', __('Error while trying to proceed with this action.'));
    }

    protected function uploadAvatar($avatar)
    {
        $user = auth()->user();

        $user->update([
            'avatar' => $avatar->store("tenant/{$user->tenant_id}/images/avatars", 'public'),
        ]);
    }
}
