<?php

namespace Logistics\Http\Controllers\Tenant\Employee;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Logistics\Http\Controllers\Controller;
use Logistics\Events\Tenant\EmployeeAvatarAdded;
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
            $this->uploadAvatar($request);

            return redirect()->route('tenant.employee.profile.edit')
                ->with('flash_success', __('Your profile has been edited.'));
        }

        return redirect()->back()
            ->with('flash_error', __('Error while trying to proceed with this action.'));
    }

    protected function uploadAvatar($request)
    {
        if ($request->hasFile('avatar')) {
            $user = auth()->user();

            if (Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            $user->update([
                'avatar' => $request->avatar->store("tenant/{$user->tenant_id}/images/avatars", 'public'),
            ]);

            event(new EmployeeAvatarAdded($user));
        }
    }
}
