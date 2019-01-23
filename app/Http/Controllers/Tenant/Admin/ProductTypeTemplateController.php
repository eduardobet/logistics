<?php

namespace Logistics\Http\Controllers\Tenant\Admin;

use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
use Logistics\DB\Tenant\Color;
use Logistics\DB\Tenant\Branch;
use Illuminate\Support\Facades\Storage;
use Logistics\Http\Controllers\Controller;
use Logistics\Events\Tenant\BranchLogoAdded;
use Logistics\Http\Requests\Tenant\BranchRequest;

class ProductTypeTemplateController extends Controller
{
    use Tenant;

    public function get()
    {
        return response()->json([
            'view' => view('tenant.branch.product-type')->render(),
        ]);
    }
}
