<?php

namespace Logistics\DB\Tenant;

use Illuminate\Database\Eloquent\Model;

class InvoiceDetail extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'invoice_id',
        'qty',
        'type',
        'length',
        'width',
        'height',
        'vol_weight',
        'real_weight',
        'description',
        'id_remote_store',
        'total',
        'vol_price',
        'real_price',
        'is_dhll',
    ];

    public function productType()
    {
        return $this->belongsTo(\Logistics\DB\Tenant\ProductType::class, 'type', 'id');
    }
}
