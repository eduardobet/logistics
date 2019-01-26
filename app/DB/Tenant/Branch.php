<?php

namespace Logistics\DB\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Logistics\DB\Tenant\ProductType;

class Branch extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'status', 'ruc', 'dv', 'telephones', 'emails', 'address', 'logo', 'code', 'created_by_code', 'faxes', 'lat', 'lng', 'tenant_id', 'updated_by_code', 'direct_comission', 'should_invoice', 'vol_price', 'real_price', 'dhl_price', 'maritime_price', 'color', 'initial', 'first_lbs_price', 'extra_maritime_price',
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

    public function productTypes()
    {
        return $this->hasMany(\Logistics\DB\Tenant\ProductType::class);
    }

    public function users()
    {
        return $this->belongsToMany(\Logistics\DB\User::class);
    }

    public function saveProductTypes($request)
    {
        $ptypes = $request->product_types ?: [];

        if (is_array($ptypes)) {
            foreach ($ptypes as $ptype) {
                $ptype = new \Illuminate\Support\Fluent($ptype);

                if (trim($ptype->name)) {
                    $this->productTypes()->updateOrCreate([
                        'branch_id' => $this->id,
                        'id' => $ptype->rid
                    ], [
                        'name' => $ptype->name,
                        'status' => $ptype->status,
                        'branch_id' => $this->id
                    ]);
                }
            }
        }
    }
}
