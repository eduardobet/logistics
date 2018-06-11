<?php

namespace Logistics\DB\Tenant;

use Illuminate\Database\Eloquent\Model;

class RemoteAddress extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id', 'status', 'type', 'telephones', 'created_by_code', 'updated_by_code', 'address',
    ];
}
