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

    // to be implemented
    public function hasActiveSubscription()
    {
        return true;
    }
}
