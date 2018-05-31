<?php

namespace Logistics\DB\Tenant;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    // to be implemented
    public function hasActiveSubscription()
    {
        return true;
    }
}
