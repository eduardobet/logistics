<?php

namespace Logistics\DB\Tenant;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'tenant_id', 'status', 'type', 'telephones', 'created_by_code', 'updated_by_code', 'pid',
        'org_name',
    ];

    public function boxes()
    {
        return $this->hasMany(\Logistics\DB\Tenant\Box::class);
    }
}
