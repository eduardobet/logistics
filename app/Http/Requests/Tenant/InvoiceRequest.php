<?php

namespace Logistics\Http\Requests\Tenant;

use Logistics\Http\Requests\AppFormRequest;

class InvoiceRequest extends AppFormRequest
{
    protected $redirectRoute = 'tenant.invoice.create';

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
            'branch_id' => 'required|integer',
            'client_id' => 'required|integer',
            'invoice_detail' => 'required|array',
            'invoice_detail.*.qty' => 'required|integer',
            'invoice_detail.*.type' => 'required|integer',
            'invoice_detail.*.description' => 'required|between:3,255',
            'invoice_detail.*.id_remote_store' => 'required',
            'invoice_detail.*.total' => 'required|numeric',

            //payment
            'amount_paid' => 'sometimes|numeric',
            'payment_method' => 'sometimes|integer',
            'payment_ref' => 'sometimes|between:3,255',
        ];

        if ($this->isEdit()) {
            $this->redirectRoute = 'tenant.invoice.edit';
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
