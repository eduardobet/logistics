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
        return view('tenant.employee.profile', [
            'employee' => auth()->user(),
        ]);
    }

    public function update(ProfileRequest $request)
    {
        $data = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'telephones' => $request->telephones,
            'pid' => $request->pid,
            'full_name' => $request->first_name . ' ' . $request->last_name,
            'address' => $request->address,
            'notes' => $request->notes,
        ];

        if ($request->new_password) {
            $data['password'] = bcrypt($request->new_password);
        }
        
        $updated = auth()->user()->update($data);


        if ($updated) {
            $this->uploadAvatar($request);

            return redirect()->route('tenant.employee.profile.edit')
                ->with('flash_success', __('Your profile has been edited.'));
        }

        return redirect()->back()
            ->with('flash_error', __('Error while trying to :action :what', [
                'action' => __('Edit'),
                'what' => __('The profile'),
            ]));
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
