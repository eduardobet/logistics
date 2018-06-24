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
        'code', 'created_by_code', 'faxes', 'lat', 'lng', 'tenant_id', 'updated_by_code', 'reception_branch', 'should_invoice',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'reception_branch' => 'boolean',
        'should_invoice' => 'boolean',
    ];

    /**
     * Boot the model
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            $key = "branches.tenant.{$model->tenant_id}";
            
            do_forget_cache(__class__, ["{$key}"]);
        });
    }
}
