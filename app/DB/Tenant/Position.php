<?php

namespace Logistics\DB\Tenant;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'status', 'created_by_code', 'tenant_id', 'updated_by_code', 'description',
    ];
}
