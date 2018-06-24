<?php

namespace Logistics\DB\Tenant;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'domain', 'name', 'status', 'ruc', 'dv', 'telephones', 'emails', 'address', 'lang', 'logo',
    ];

    /**
     * Boot the model
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        $host = get_host();

        do_forget_cache(__class__, ["{$host}"]);
    }

    public function employees()
    {
        return $this->hasMany(\Logistics\DB\User::class);
    }

    public function branches()
    {
        return $this->hasMany(\Logistics\DB\Tenant\Branch::class);
    }

    public function clients()
    {
        return $this->hasMany(\Logistics\DB\Tenant\Client::class);
    }

    public function remoteAddresses()
    {
        return $this->hasMany(\Logistics\DB\Tenant\RemoteAddress::class);
    }

    public function positions()
    {
        return $this->hasMany(\Logistics\DB\Tenant\Position::class);
    }

    public function permissions()
    {
        return $this->hasMany(\Logistics\DB\Tenant\Permission::class);
    }

    public function mailers()
    {
        return $this->hasMany(\Logistics\DB\Tenant\Mailer::class);
    }

    public function warehouses()
    {
        return $this->hasMany(\Logistics\DB\Tenant\Warehouse::class);
    }

    // to be implemented
    public function hasActiveSubscription()
    {
        return true;
    }

    /**
     * Format env file content
     *
     * @param  string $domainName
     * @return string
     */
    private function getContent(string $domainName)
    {
        return $content = "APP_URL={$this->domain}\nAPP_DOMAIN={$this->domain}\nAPP_NAME=\"{$this->name}\"\nSESSION_DOMAIN={$domainName}";
    }

    /**
     * Create the env file for the current saved app
     *
     * @return string
     */
    public function touchEnvFile()
    {
        $hostParts = explode('//', $this->domain);
        $domainName = $hostParts[1];
        $envFile = base_path('envs/' . $domainName);

        \Illuminate\Support\Facades\File::put($envFile, $this->getContent($domainName));

        \Illuminate\Support\Facades\Artisan::call('config:clear');

        return $envFile;
    }
}
