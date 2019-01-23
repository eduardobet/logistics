<?php

namespace Logistics\Http\Requests\Tenant;

use Illuminate\Validation\Rule;
use Logistics\Http\Requests\AppFormRequest;

class BranchRequest extends AppFormRequest
{
    protected $redirectRoute = 'tenant.admin.branch.create';

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
            'name' => 'required|string|between:3,255|unique:branches',
            'address' => 'required|string|between:10,255',
            'emails' => 'required|string|mass_email',
            'telephones' => 'required|string|mass_phone',
            'faxes' => 'nullable|string|mass_phone',
            'code' => 'required|string',
            'initial' => 'required|string',
            'status' => 'required|string|in:A,I',
            'real_price' => 'sometimes|numeric',
            'vol_price' => 'sometimes|numeric',
            'dhl_price' => 'sometimes|numeric',
            'maritime_price' => 'sometimes|numeric',
            'first_lbs_price' => 'sometimes|numeric',
            'extra_maritime_price' => 'sometimes|numeric',
            'color' => 'required',
            'logo' => 'sometimes|mimes:png,jpg,jpeg|max:1024',

            'product_types' => 'sometimes|array',
            'product_types.*.name' => 'sometimes|between:3,255',
            'product_types.*.status' => 'sometimes|in:A,I',
        ];

        if ($this->isEdit()) {
            $rules['name'] = ['required', 'string', 'between:3,255', Rule::unique('branches')->ignore($this->id)];

            $this->redirectRoute = 'tenant.admin.branch.edit';
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
            array_merge(
                $params,
                $this->query()
            )
        );
    }
}
