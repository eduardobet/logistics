<?php

namespace Logistics\DB\Tenant;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    /**
     * Prevent id from being mass assigned.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Gets country for input dropdown list.
     *
     * @param mixed $tenantId
     * @return \Illuminate\Support\Collection
     */
    public function getCountryAsList($tenantId)
    {
        $key = "country.list.{$tenantId}";

        $c = cache()->get($key, function () use ($key, $tenantId) {
            $c = $this->where('tenant_id', $tenantId)
                ->orderBy('name')->pluck('name', 'id');

            cache()->forever($key, $c);

            return $c;
        });

        return $c;
    }
}
