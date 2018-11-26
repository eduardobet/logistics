<?php

namespace Logistics\DB\Tenant;

use Illuminate\Support\Facades\Config;
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
        'mail_from_name', 'mailgun_domain', 'mailgun_secret', 'migration_mode', 'email_allowed_dup',
    ];

    /**
     * Boot the model
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::saved(function ($tenant) {
            __do_forget_cache(__class__, ["{$tenant->domain}"], []);
        });
    }

    // to be implemented
    public function hasActiveSubscription()
    {
        return true;
    }

    public function setConfigs()
    {
        Config::set('mail.from.address', $this->mail_from_address);
        Config::set('mail.from.name', $this->mail_from_name);
        Config::set('mail.driver', $this->mail_driver);
        Config::set('mail.host', $this->mail_host);
        Config::set('mail.port', $this->mail_port);
        Config::set('mail.encryption', $this->mail_encryption);
        Config::set('mail.username', $this->mail_username);
        Config::set('mail.password', $this->mail_password);
        Config::set('services.mailgun.domain', $this->mailgun_domain);
        Config::set('services.mailgun.secret', $this->mailgun_secret);

        // app
        Config::set('app.name', $this->name);
        Config::set('app.url', $this->domain);
        Config::set('app.domain', $this->domain);
        Config::set('app.locale', $this->lang);
        Config::set('app.country', $this->country_id);
        Config::set('app.timezone', $this->timezone);

        Config::set('session.domain', $this->domain);
    }
}
