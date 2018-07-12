<?php

namespace Logistics\DB\Tenant;

use Illuminate\Support\Fluent;
use Illuminate\Database\Eloquent\Model;
use Logistics\Traits\WarehouseHasRelationShips;
use Logistics\Notifications\Tenant\WarehouseActivity;

class Warehouse extends Model
{
    use WarehouseHasRelationShips;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'branch_from', 'branch_to','mailer_id','trackings','reference','qty', 'created_by_code', 'tenant_id', 'updated_by_code', 'client_id', 'type',
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
