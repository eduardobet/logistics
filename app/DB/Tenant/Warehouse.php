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
        'tot_weight', 'tot_packages', 'force_updated_at',
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

        static::saved(function ($model) {
            $keys = ["warehouses.tenant.{$model->tenant_id}"];

            do_forget_cache(__class__, $keys);
        });
    }
}
