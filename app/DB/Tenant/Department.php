<?php

namespace Logistics\DB\Tenant;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    /**
     * Prevent id from being mass assigned.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Gets department for input dropdown list.
     *
     * @param mixed $tenantId
     * @return \Illuminate\Support\Collection
     */
    public function getDepartmentAsList($countryId)
    {
        $key = "department.list.{$countryId}";

        $depts = cache()->get($key, function () use ($key, $countryId) {
            $depts = $this->where('country_id', $countryId)
                ->orderBy('name')->pluck('name', 'id');

            cache()->forever($key, $depts);

            return $depts;
        });

        return $depts;
    }
}
