<?php

namespace Logistics\Support;

use Logistics\Traits\Tenant;
use Illuminate\Mail\TransportManager;
use Illuminate\Foundation\Application;

class CustomMailTransportManager extends TransportManager
{
    use Tenant;

    public function __construct(Application $app)
    {
        $configs = [];

        $this->app = $app;

        
        if (!app()->environment('testing')) {
            $tenant = $this->getTenant();

            if (!$tenant) {
                $tenant = app('tenant');
            }

            if ($tenant) {
                $configs = [
                    'driver' => $tenant->mail_driver,
                    'host' => $tenant->mail_host,
                    'port' => $tenant->mail_port,
                    'encryption' => $tenant->mail_encryption,
                    'username' => $tenant->mail_username,
                    'password' => $tenant->mail_password,
                    'from' => [
                        'address' => $tenant->mail_from_address,
                        'name' => $tenant->mail_from_name,
                    ],
                ];
            
            
                $this->setDefaultDriver('smtp');

                $this->app['config']['mail'] = array_merge($configs, [
                    'sendmail' => '/usr/sbin/sendmail -bs',
                    'pretend' => false,
                ]);

                $this->app['config']['services'] = [
                    'mailgun' => [
                        'domain' => $tenant->mailgun_domain,
                        'secret' => $tenant->mailgun_secret,
                    ]
                ];

                $this->app['config']['app'] = [
                    'name' => $tenant->name,
                    'url' => $tenant->domain,
                    'domain' => $tenant->domain,
                    'locale' => $tenant->lang,
                    'country' => $tenant->country_id,
                    'timezone' => $tenant->timezone,
                ];
            }
        }
    }
}
