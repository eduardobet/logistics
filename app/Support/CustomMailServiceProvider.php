<?php

namespace Logistics\Support;

use Illuminate\Mail\MailServiceProvider;
use Logistics\Support\CustomMailTransportManager;

class CustomMailServiceProvider extends MailServiceProvider
{
    protected function registerSwiftTransport()
    {
        $this->app->singleton('swift.transport', function ($app) {
            return new CustomMailTransportManager($app);
        });
    }
}
