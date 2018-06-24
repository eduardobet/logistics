<?php

namespace Logistics\DB\Tenant;

use Illuminate\Database\Eloquent\Model;

class BranchForInvoice extends Model
{
    protected $table = 'branch_for_invoice';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
