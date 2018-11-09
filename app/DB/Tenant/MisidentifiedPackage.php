<?php

namespace Logistics\DB\Tenant;

use Illuminate\Database\Eloquent\Model;

class MisidentifiedPackage extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id', 'client_id', 'branch_to', 'status', 'trackings', 'cargo_entry_id',
    ];

    public function toBranch()
    {
        return $this->belongsTo(\Logistics\DB\Tenant\Branch::class, 'branch_to');
    }

    public function client()
    {
        return $this->belongsTo(\Logistics\DB\Tenant\Client::class);
    }

    public function cargoEntry()
    {
        return $this->belongsTo(\Logistics\DB\Tenant\CargoEntry::class);
    }
}
