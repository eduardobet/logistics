<?php

namespace Logistics\DB\Tenant;

use Illuminate\Database\Eloquent\Model;

class Box extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id', 'client_id', 'branch_id', 'status', 'branch_code',
    ];

    /**
     * Scope a query to only include active boxes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'A');
    }
}
