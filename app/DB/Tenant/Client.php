<?php

namespace Logistics\DB\Tenant;

use Logistics\Traits\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Logistics\Traits\ClientHasRelationShips;

class Client extends Model
{
    use Tenant, ClientHasRelationShips, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'tenant_id', 'status', 'type', 'telephones', 'created_by_code', 'updated_by_code', 'pid',
        'org_name', 'country_id', 'department_id', 'city_id', 'notes', 'pay_volume', 'special_rate', 'special_maritime', 'address',
        'vol_price', 'real_price', 'full_name', 'manual_id', 'branch_id', 'first_lbs_price', 'pay_first_lbs_price',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'manual_id' => 'integer',
        'pay_volume' => 'boolean',
        'pay_first_lbs_price' => 'boolean',
        'special_rate' => 'boolean',
        'special_maritime' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['full_name', 'manual_id_dsp'];

    /**
     * Boot the model
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            $branch = request('branch_id');
            $keys = ["clients.tenant.{$model->tenant_id}", ];

            __do_forget_cache(__class__, $keys, []);
        });
    }

    public function creator()
    {
        return $this->belongsTo(\Logistics\DB\User::class, 'created_by_code')
            ->select('id', 'first_name', 'last_name');
    }

    public function editor()
    {
        return $this->belongsTo(\Logistics\DB\User::class, 'updated_by_code')
            ->select('id', 'first_name', 'last_name');
    }
}
