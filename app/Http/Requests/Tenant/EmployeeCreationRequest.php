<?php

namespace Logistics\Http\Requests\Tenant;

use Logistics\Traits\Tenant;
use Illuminate\Validation\Rule;
use Logistics\Http\Requests\AppFormRequest;

class EmployeeCreationRequest extends AppFormRequest
{
    use Tenant;
   
    protected $redirectRoute = 'tenant.admin.employee.create';

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $tenant = $this->getTenant();

        $rules = [
            'first_name' => 'required|string|between:3,255',
            'last_name' => 'required|string|between:3,255',
            'email' => 'required|string|email|max:255|unique:users',
            'type' => 'required|string|in:A,E,C',
            'status' => 'required|string|in:L',
            'branches' => 'required',
            'pid' => 'required',
            'position' => 'required',
            'telephones' => 'required|mass_phone',
            'permissions' => 'sometimes|array',
            'branches_for_invoices' => 'sometimes|array',
            'password' => 'sometimes|alpha_num_pwd',
        ];

        $unique = Rule::unique('users', 'email')->where('tenant_id', $tenant->id);

        if ($this->isEdit()) {
            $rules['email'] = ['required', 'string', 'email', 'between:6,255', $unique->ignore($this->id)];
            $rules['status'] = 'required|string|in:A,I,L'; //|user_status:id

            $this->redirectRoute = 'tenant.admin.employee.edit';
        } else {
            $rules['email'] = ['required', 'string', 'email', 'between:6,255', $unique];
        }

        return $rules;
    }

    /**
     * Get the URL to redirect to on a validation error.
     *
     * @return string
     */
    protected function getRedirectUrl()
    {
        $url = $this->redirector->getUrlGenerator();
        $params = ['domain' => $this->domain];

        if ($this->isEdit()) {
            $params['id'] = $this->id;
        }

        return $url->route(
            $this->redirectRoute,
            array_merge($params, $this->query())
        );
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
              'status.user_status' => __('You cannot change the user from locked to active.'),
         ];
    }
}
