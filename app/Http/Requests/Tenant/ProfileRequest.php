<?php

namespace Logistics\Http\Requests\Tenant;

use Logistics\Http\Requests\AppFormRequest;

class ProfileRequest extends AppFormRequest
{
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
            'avatar' => 'sometimes|mimes:png,jpg,jpeg|max:1024',
            'email' => 'not_present',
            'type' => 'not_present',
            'status' => 'not_present',
            'branches' => 'not_present',
            'is_main_admin' => 'not_present',
        ];

        if ($this->has('current_password') && $this->current_password) {
            $rules['current_password'] = 'required|pass_check';
            $rules['new_password'] = 'required|alpha_num_pwd|confirmed';
            $rules['new_password_confirmation'] = 'required';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        $illegalFields = [ 'email', 'type', 'status', 'branches', ];
        $messages = [];

        foreach ($illegalFields as $key => $attribute) {
            $messages["{$attribute}.not_present"] = __('The field :attribute must not be present.');
        }

        return $messages;
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

        return $url->route(
            'tenant.employee.profile.edit',
            array_merge($params, $this->query())
        );
    }
}
