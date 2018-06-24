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
            'branch_to_issue' => 'required|integer',
            'reception_branch' => 'sometimes|integer',
            'mailer_id' => 'required|integer',
            'client_id' => 'required|integer',
            'trackings' => 'required|string',
            'reference' => 'required|string',
            'qty' => 'required|integer',
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
