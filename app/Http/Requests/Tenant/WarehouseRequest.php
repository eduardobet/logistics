<?php

namespace Logistics\Http\Requests\Tenant;

use Logistics\Http\Requests\AppFormRequest;

class WarehouseRequest extends AppFormRequest
{
    protected $redirectRoute = 'tenant.warehouse.create';

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
            'branch_from' => 'required|integer',
            'branch_to' => 'required|integer',
            'client_id' => 'sometimes|integer',
            'reference' => 'sometimes|string',
            'trackings' => 'sometimes|string',
            'type' => 'required|in:A,M',
            'tot_packages' => 'required|integer',
            'tot_weight' => 'required|integer',
        ];

        if ($this->isEdit()) {
            $this->redirectRoute = 'tenant.warehouse.edit';
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
