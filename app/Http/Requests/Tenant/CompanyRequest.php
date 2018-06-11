<?php

namespace Logistics\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends FormRequest
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
        return [
            'status' => 'not_present',
            'domain' => 'not_present',
            'name' => 'required|string|between:3,255',
            'telephones' => 'required|mass_phone',
            'emails' => 'required|mass_email',
            'address' => 'required|string|between:10,255',
            'lang' => 'required|in:en,es',
            'logo' => 'sometimes|mimes:png,jpg,jpeg|max:1024',
            'remote_addresses' => 'required',
            'remote_addresses.*.type' => 'required',
            'remote_addresses.*.address' => 'required',
            'remote_addresses.*.telephones' => 'required',
            'remote_addresses.*.status' => 'required',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        $illegalFields = [ 'domain', 'status', ];
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
            'tenant.admin.company.edit',
            array_merge($params, $this->query())
        );
    }
}
