<?php

namespace Logistics\Providers;

use Storage;
use League\Flysystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Spatie\FlysystemDropbox\DropboxAdapter;
use Spatie\Dropbox\Client as DropboxClient;

class DropboxServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Storage::extend('dropbox', function ($app, $config) {
            $client = new DropboxClient(
                $config['authorizationToken']
            );

            return new Filesystem(new DropboxAdapter($client));
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
