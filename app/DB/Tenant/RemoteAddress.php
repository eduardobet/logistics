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

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($query) {
            $query->created_by_code = auth()->id();
        });

        static::updating(function ($query) {
            $query->updated_by_code = auth()->id();
        });
    }
}
