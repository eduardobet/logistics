<?php

namespace Logistics\DB\Tenant;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    /**
     * Prevent id from being mass assigned.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Gets zone for input dropdown list.
     *
     * @param mixed $tenantId
     * @return \Illuminate\Support\Collection
     */
    public function getZoneAsList($departmentId)
    {
        $key = "zone.list.{$departmentId}";

        $depts = cache()->get($key, function () use ($key, $departmentId) {
            $depts = $this->where('department_id', $departmentId)
                ->orderBy('name')->pluck('name', 'id');

            cache()->forever($key, $depts);

            return $depts;
        });

        return $depts;
    }
}
