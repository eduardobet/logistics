<?php

namespace Logistics\DB\Tenant;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'status', 'ruc', 'dv', 'telephones', 'emails', 'address', 'logo',
        'code', 'created_by_code', 'faxes', 'lat', 'lng', 'tenant_id', 'updated_by_code',
    ];
}
