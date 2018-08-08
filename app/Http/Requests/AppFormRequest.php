<?php

namespace Logistics\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Auth\Access\AuthorizationException;

abstract class AppFormRequest extends FormRequest
{
    /**
     * Get all of the inputs and files for the request.
     *
     * @return array
     */
    public function all($keys = null)
    {
        $inputs = parent::all($keys);
        $toBeIgnored = config('app.ignore_clean', []);
        $toBeCleaned = array_except($inputs, $toBeIgnored);
        $ignored = array_only($inputs, $toBeIgnored);
        $cleaned = clean($toBeCleaned);

        $this->replace(array_merge($ignored, $cleaned));

        return parent::all($keys);
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function failedAuthorization()
    {
        throw new AuthorizationException(__('This action is unauthorized.'));
    }

    /**
     * Checking for POST request.
     */
    public function isPost()
    {
        $method = strtolower($this->method());

        return $method == 'post';
    }

    /**
     * Checking for PATCH/PUT request.
     */
    public function isEdit()
    {
        $method = strtolower($this->method());

        return $method == 'patch' || $method == 'put';
    }
}
