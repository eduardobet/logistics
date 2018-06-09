<?php

namespace Logistics\DB\Tenant;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'tenant_id', 'status', 'type', 'telephones', 'created_by_code', 'updated_by_code', 'pid',
        'org_name', 'country_id', 'department_id', 'city_id', 'notes', 'pay_volume', 'special_rate', 'special_maritime',
    ];

    public function boxes()
    {
        return $this->hasMany(\Logistics\DB\Tenant\Box::class);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function genBox($branchId, $branchCode)
    {
        $boxes = $this->boxes()->active()->get();

        if ($boxes->count()) {
            foreach ($boxes as $box) {
                $box->update([
                    'status' => 'I',
                ]);
            }
        }

        $this->boxes()->create([
            'tenant_id' => $this->tenant_id,
            'status' => 'A',
            'branch_id' => $branchId,
            'branch_code' => $branchCode,
        ]);
    }
}
