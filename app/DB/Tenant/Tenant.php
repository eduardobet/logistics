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
        'domain', 'name', 'status', 'ruc', 'dv', 'telephones', 'emails', 'address', 'lang', 'logo', 'country_id', 'timezone',
        'mail_driver', 'mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption', 'mail_from_address',
        'mail_from_name', 'mailgun_domain', 'mailgun_secret',
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

        static::saved(function ($tenant) {
            $tenant->touchEnvFile();
        });
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
        return $content = "APP_URL={$this->domain}\nAPP_DOMAIN={$this->domain}\nAPP_NAME=\"{$this->name}\"\nSESSION_DOMAIN={$domainName}\nTENANT_COUNTRY={$this->country_id}\nTENANT_TIMEZONE={$this->timezone}\nMAIL_DRIVER={$this->mail_driver}\nMAIL_HOST={$this->mail_host}\nMAIL_PORT={$this->mail_port}\nMAIL_USERNAME={$this->mail_username}\nMAIL_PASSWORD={$this->mail_password}\nMAIL_ENCRYPTION={$this->mail_encryption}\nMAIL_FROM_ADDRESS={$this->mail_from_address}\nMAIL_FROM_NAME=\"{$this->mail_from_name}\"\nMAILGUN_DOMAIN={$this->mailgun_domain}\nMAILGUN_SECRET={$this->mailgun_secret}";
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

        try {
            \Illuminate\Support\Facades\File::put($envFile, $this->getContent($domainName));
        } catch (\Exception $e) {
        }


        //\Illuminate\Support\Facades\Artisan::call('config:clear');

        return $envFile;
    }

    public function touchEnvFileForConsole()
    {
        config([
            'app.name' => $this->name,
            'app.url' => $this->domain,
            'app.locale' => $this->lang,
            'app.country' => $this->country_id,
            'app.timezone' => $this->timezone,
        ]);
    }
}
