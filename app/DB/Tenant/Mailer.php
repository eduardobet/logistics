<?php

namespace Logistics\DB\Tenant;

use Illuminate\Database\Eloquent\Model;

class Mailer extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'status', 'created_by_code', 'tenant_id', 'updated_by_code', 'description',
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
            $key = "mailers.tenant.{$model->tenant_id}";
            
            do_forget_cache(__class__, ["{$key}"]);
        });
    }
}
