<?php

namespace Logistics\Http\Requests\Tenant;

use Illuminate\Validation\Rule;
use Logistics\Http\Requests\AppFormRequest;

class EmployeeCreationRequest extends AppFormRequest
{
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
        $rules = [
            'first_name' => 'required|string|between:3,255',
            'last_name' => 'required|string|between:3,255',
            'email' => 'required|string|email|max:255|unique:users',
            'type' => 'required|string|in:A,E',
            'status' => 'required|string|in:L',
            'branches' => 'required',
            'pid' => 'required',
            'position' => 'required',
            'telephones' => 'required|mass_phone',
            'permissions' => 'sometimes|array',
            'branches_for_invoices' => 'sometimes|array',
        ];

        if ($this->isEdit()) {
            $rules['email'] = ['required', 'string', 'email', 'between:6,255', Rule::unique('users')->ignore($this->id)];
            $rules['status'] = 'required|string|in:A,I,L|user_status:id';

            $this->redirectRoute = 'tenant.admin.employee.edit';
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
        $params = [];

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
