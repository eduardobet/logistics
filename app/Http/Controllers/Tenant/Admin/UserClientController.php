<?php

namespace Logistics\Http\Controllers\Tenant\Admin;

use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
use Illuminate\Validation\Rule;
use Logistics\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class UserClientController extends Controller
{
    use Tenant;

    public function store(Request $request)
    {
        $validator = $this->validates($request);
        
        if ($validator->fails()) {
            return response()->json([
                'msg' => $validator->errors()->first(),
                'error' => true,
            ], 500);
        }

        $tenant = $this->getTenant();

        $user = $tenant->userClients()->create([
            'client_id' => $request->client_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'type' => 'C',
            'full_name' => $request->first_name . ' ' . $request->last_name,
            'telephones' => $request->telephones,
            'created_by_code' => auth()->id(),
            'password' => $request->password ? bcrypt($request->password) : null,
            'status' => 'A',
        ]);

        if ($user) {
            $user->branches()->sync($request->branch_id);

            return response()->json([
                'error' => false,
                'msg' => __('Success'),
            ], 200);
        }

        return response()->json([
            'error' => true,
            'msg' => __('Error'),
        ], 500);
    }

    private function validates($request, $extraRules = [])
    {
        $tenant = $this->getTenant();

        $rules = [
            'first_name' => 'required|string|between:3,255',
            'last_name' => 'required|string|between:3,255',
            'telephones' => 'required|mass_phone',
            'password' => 'sometimes|alpha_num_pwd',
            'client_id' => 'required|integer',
            'branch_id' => 'required|integer',
        ];

        $rules['email'] = [
            'required', 'string', 'email', 'max:255',
            Rule::unique('users', 'email')->where('tenant_id', $tenant->id)
        ];

        return Validator::make($request->all(), array_merge($rules, $extraRules));
    }
}
