<?php

namespace Logistics\Http\Requests\Tenant;

use Illuminate\Validation\Rule;
use Logistics\Http\Requests\AppFormRequest;

class PositionRequest extends AppFormRequest
{
    protected $redirectRoute = 'tenant.admin.position.create';

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
            'name' => 'required|string|between:3,255|unique:positions',
            'status' => 'required|string|in:A,I',
            'description' => 'nullable|string',
        ];

        if ($this->isEdit()) {
            $rules['name'] = ['required', 'string', 'between:3,255', Rule::unique('positions')->ignore($this->id)];

            $this->redirectRoute = 'tenant.admin.position.edit';
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
            array_merge(
                $params,
                $this->query()
            )
        );
    }
}
