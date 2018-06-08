<?php

namespace Logistics\DB\Tenant;

use Illuminate\Database\Eloquent\Model;

class Box extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id', 'client_id', 'branch_id', 'status', 'branch_code',
    ];
}
