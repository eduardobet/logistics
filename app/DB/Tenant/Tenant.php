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
        'domain', 'name', 'status',
    ];

    public function employees()
    {
    	return $this->hasMany(\Logistics\DB\Tenant\User::class);
    }

    // to be implemented
    public function hasActiveSubscription()
    {
        return true;
    }
}
