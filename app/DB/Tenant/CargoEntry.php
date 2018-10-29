<?php

namespace Logistics\DB\Tenant;

use Illuminate\Database\Eloquent\Model;

class CargoEntry extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'created_by_code', 'tenant_id', 'updated_by_code', 'branch_id', 'trackings', 'type',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['created_at_dsp'];

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

    public function creator()
    {
        return $this->belongsTo(\Logistics\DB\User::class, 'created_by_code');
    }

    /**
     * Get created at for display.
     *
     * @param  string  $value
     * @return string
     */
    public function getCreatedAtDspAttribute($value)
    {
        return $this->created_at->format('d-m-Y H:i a');
    }
}
