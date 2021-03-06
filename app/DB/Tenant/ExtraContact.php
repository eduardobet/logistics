<?php

namespace Logistics\DB\Tenant;

use Illuminate\Database\Eloquent\Model;

class ExtraContact extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'full_name', 'pid', 'created_by_code', 'tenant_id', 'updated_by_code', 'client_id', 'email', 'telephones', 'receive_inv_mail', 'receive_wh_mail',
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
