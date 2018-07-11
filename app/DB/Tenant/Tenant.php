<?php

namespace Logistics\DB\Tenant;

use Illuminate\Database\Eloquent\Model;
use Logistics\Traits\TenantHasRelationships;

class Tenant extends Model
{
    use TenantHasRelationships;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'domain', 'name', 'status', 'ruc', 'dv', 'telephones', 'emails', 'address', 'lang', 'logo', 'country_id',
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
        return $content = "APP_URL={$this->domain}\nAPP_DOMAIN={$this->domain}\nAPP_NAME=\"{$this->name}\"\nSESSION_DOMAIN={$domainName}\nTENANT_COUNTRY={$this->country_id}";
    }

    /**
     * Create the env file for the current saved app
     *
     * @return string
     */
    public function touchEnvFile()
    {
        $hostParts = explode('//', $this->domain);
        $domainName = $this->domain; //$hostParts[1];
        $envFile = base_path('envs/' . $domainName);

        \Illuminate\Support\Facades\File::put($envFile, $this->getContent($domainName));

        \Illuminate\Support\Facades\Artisan::call('config:clear');

        return $envFile;
    }

    public function touchEnvFileForConsole()
    {
        config([
            'app.name' => $this->name,
            'app.url' => $this->domain,
            'app.locale' => $this->lang,
            'app.country' => $this->country_id,
        ]);
    }
}
