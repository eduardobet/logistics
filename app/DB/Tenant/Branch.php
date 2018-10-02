<?php

namespace Logistics\DB\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Branch extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'status', 'ruc', 'dv', 'telephones', 'emails', 'address', 'logo', 'code', 'created_by_code', 'faxes', 'lat', 'lng', 'tenant_id', 'updated_by_code', 'direct_comission', 'should_invoice', 'vol_price', 'real_price', 'dhl_price', 'maritime_price', 'color',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'direct_comission' => 'boolean',
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
            $keys = ["branches.tenant.{$model->tenant_id}", "employee.branches.{$model->tenant_id}"];
            
            __do_forget_cache(__class__, $keys, []);
        });
    }

    public function invoices()
    {
        return $this->hasMany(\Logistics\DB\Tenant\Invoice::class);
    }
}
