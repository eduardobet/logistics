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
     * @return \Illuminate\Support\Collection
     */
    public function getCountryAsList()
    {
        $key = "country.list";

        $c = cache()->get($key, function () use ($key) {
            $c = $this->where('status', 'A')
                ->orderBy('name')->pluck('name', 'id');

            cache()->forever($key, $c);

            return $c;
        });

        return $c;
    }
}
