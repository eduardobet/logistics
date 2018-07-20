<?php

namespace Logistics\DB\Tenant;

use Logistics\DB\User;
use Logistics\DB\Tenant\Payment;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'created_by_code', 'tenant_id', 'updated_by_code', 'branch_id', 'client_name', 'client_email', 'status', 'volumetric_weight', 'real_weight', 'total',
        'notes', 'warehouse_id', 'client_id',
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

    public function details()
    {
        return $this->hasMany(InvoiceDetail::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_code');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
