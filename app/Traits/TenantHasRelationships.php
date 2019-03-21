<?php

namespace Logistics\Traits;

trait TenantHasRelationships
{
    public function country()
    {
        return $this->belongsTo(\Logistics\DB\Tenant\Country::class);
    }

    public function employees()
    {
        return $this->hasMany(\Logistics\DB\User::class);
    }

    public function userClients()
    {
        return $this->hasMany(\Logistics\DB\User::class)->where('type', 'C');
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
        return $this->hasMany(\Logistics\DB\Tenant\Permission::class)->orderBy('header');
    }

    public function mailers()
    {
        return $this->hasMany(\Logistics\DB\Tenant\Mailer::class);
    }

    public function warehouses()
    {
        return $this->hasMany(\Logistics\DB\Tenant\Warehouse::class);
    }

    public function invoices()
    {
        return $this->hasMany(\Logistics\DB\Tenant\Invoice::class);
    }

    public function conditions()
    {
        return $this->hasMany(\Logistics\DB\Tenant\Condition::class);
    }

    public function conditionsInvoice()
    {
        return $this->hasMany(\Logistics\DB\Tenant\Condition::class)->where('type', 'I')->where('status', 'A');
    }

    public function conditionsWarehouse()
    {
        return $this->hasMany(\Logistics\DB\Tenant\Condition::class)->where('type', 'W')->where('status', 'A');
    }

    public function payments()
    {
        return $this->hasMany(\Logistics\DB\Tenant\Payment::class);
    }

    public function cargoEntries()
    {
        return $this->hasMany(\Logistics\DB\Tenant\CargoEntry::class);
    }

    public function misidentifiedPackages()
    {
        return $this->hasMany(\Logistics\DB\Tenant\MisidentifiedPackage::class);
    }

    /**
     * Get the telephones for display.
     *
     * @param  string  $value
     * @return string
     */
    public function getTelephonesDspAttribute($value)
    {
        return getEntXtra($this->telephones);
    }

    /**
     * Get the emails for display.
     *
     * @param  string  $value
     * @return string
     */
    public function getEmailsDspAttribute($value)
    {
        return getEntXtra($this->emails);
    }
}
