<?php

namespace Logistics\Http\Requests\Tenant;

use Illuminate\Validation\Rule;
use Logistics\Http\Requests\AppFormRequest;

class MailerRequest extends AppFormRequest
{
    protected $redirectRoute = 'tenant.mailer.create';

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
            'tenant_id' => 'required',
            'mailers' => 'required|array',
            'mailers.*.status' => 'required|in:A,I',
            'mailers.*.vol_price' => 'sometimes|numeric',
            'mailers.*.real_price' => 'sometimes|numeric',
            'mailers.*.description' => 'sometimes|between:3,500',
        ];

        if ($this->isEdit()) {
            $rules = array_merge($rules, $this->getNameRules());
            
            $this->redirectRoute = 'tenant.mailer.edit';
        } else {
            $rules['mailers.*.name'] = ['required', 'string', 'between:3,255', Rule::unique('mailers')->where('tenant_id', $this->tenant_id)];
        }

        return $rules;
    }

    private function getNameRules()
    {
        $rules = [];

        foreach ($this->mailers as $key => $input) {
            $rules["mailers.{$key}.name"] = ['required', 'string', 'between:3,255',Rule::unique('mailers', 'name')
                    ->where('tenant_id', $this->tenant_id)->ignore($input['mid'])];
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
