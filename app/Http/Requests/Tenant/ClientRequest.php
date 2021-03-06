<?php

namespace Logistics\Http\Requests\Tenant;

use Logistics\Traits\Tenant;
use Illuminate\Validation\Rule;
use Logistics\Http\Requests\AppFormRequest;

class ClientRequest extends AppFormRequest
{
    use Tenant;
    
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
        $tenant = $this->getTenant();

        $rules = [
            'first_name' => 'required|string|between:3,255',
            'last_name' => 'required|string|between:3,255',
            'pid' => 'required|string',
            'telephones' => 'required|mass_phone',
            'type' => 'required|string|in:C,V,E',
            'org_name' => 'required_if:type,==,E|string|between:3,255',
            'status' => 'required|string|in:A,I',
            'branch_id' => 'required',
            'branch_code' => 'required',
            'country_id' => 'sometimes|integer',
            'department_id' => 'sometimes|integer',
            'city_id' => 'sometimes|integer',
            'address' => 'sometimes|between:5,255',
            'notes' => 'sometimes|between:5,1000',
            'vol_price' => 'sometimes|numeric',
            'real_price' => 'sometimes|numeric',
            'first_lbs_price' => 'sometimes|numeric',
            'maritime_price' => 'sometimes|numeric',
            'extra_maritime_price' => 'sometimes|numeric',
        ];

        if ($this->isEdit()) {
            $rules['email'] = ['required', 'string', 'email', 'max:255', ];

            if ($tenant->email_allowed_dup && $this->email !== $tenant->email_allowed_dup) {
                //$rules['email'] = [Rule::unique('clients')->ignore($this->id)];
            }

            $this->redirectRoute = 'tenant.client.edit';
        } else {
            $rules['email'] = ['required', 'string', 'email', 'max:255', ];

            if ($tenant->email_allowed_dup && $this->email !== $tenant->email_allowed_dup) {
                //$rules['email'] = [Rule::unique('clients')];
            }
        }

        if ($this->manual_id && $this->isPost()) {
            $rules['manual_id'] = ['required','integer', Rule::unique('clients', 'manual_id')
                ->where('branch_id', $this->branch_id)
                ->where('status', 'A')
            ];
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
}
