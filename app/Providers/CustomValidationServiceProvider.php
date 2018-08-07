<?php

namespace Logistics\Providers;

use Illuminate\Support\ServiceProvider;
use Logistics\Validator\CustomValidator;

class CustomValidationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->validator->resolver(function ($translator, $data, $rules, $messages, $customAttributes) {
            return new CustomValidator($translator, $data, $rules, $messages, $customAttributes);
        });
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
