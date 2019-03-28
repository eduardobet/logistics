<?php

namespace Logistics\DB\Tenant;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'created_by_code', 'tenant_id', 'updated_by_code', 'invoice_id', 'amount_paid', 'payment_method', 'payment_ref', 'is_first', 'created_at',
        'notes', 'status',
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

    public function invoice()
    {
        return $this->belongsTo(\Logistics\DB\Tenant\Invoice::class);
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

    public function scopeWithAndWhereHas($query, $relation, $constraint)
    {
        return $query->whereHas($relation, $constraint)
            ->with([$relation => $constraint]);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'A');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'I');
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
