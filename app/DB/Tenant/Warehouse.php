<?php

namespace Logistics\DB\Tenant;

use Illuminate\Support\Fluent;
use Illuminate\Database\Eloquent\Model;
use Logistics\Traits\WarehouseHasRelationShips;

class Warehouse extends Model
{
    use WarehouseHasRelationShips;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'branch_from', 'branch_to','mailer_id','trackings','reference','qty', 'created_by_code', 'tenant_id', 'updated_by_code', 'client_id', 'type','tot_weight', 'tot_packages', 'force_updated_at', 'manual_id', 'status', 'created_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'manual_id' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['created_at_dsp', 'manual_id_dsp'];

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

            __do_forget_cache(__class__, $keys, []);
        });
    }
}
