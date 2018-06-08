<?php

namespace Logistics\Http\Requests\Tenant;

use Logistics\Http\Requests\AppFormRequest;

class ClientRequest extends AppFormRequest
{
    protected $redirectRoute = 'tenant.client.create';

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
            'pid' => 'required|string',
            'email' => 'required|string|email|max:255|unique:clients',
            'telephones' => 'required|mass_phone',
            'type' => 'required|string|in:C,V,E',
            'org_name' => 'required_if:type,==,E|string',
            'status' => 'required|string|in:A,I',
            'branch_id' => 'required',
        ];

        if ($this->isEdit()) {
            $rules['email'] = ['required', 'string', 'email', 'max:255', Rule::unique('clients')->ignore($this->id)];

            $this->redirectRoute = 'tenant.client.edit';
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
}
