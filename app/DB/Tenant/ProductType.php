<?php

namespace Logistics\DB\Tenant;

use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'branch_id', 'status', 'created_by_code', 'updated_by_code', 'name',
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

    public function branch()
    {
        return $this->belongsTo(\Logistics\DB\Tenant\Branch::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'A');
    }
}
