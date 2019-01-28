<?php

namespace Logistics\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
use Logistics\Http\Controllers\Controller;

class IncomeController extends Controller
{
    use Tenant;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tenant = $this->getTenant();

        $branches = $this->getBranches();

        if (!auth()->user()->isSuperAdmin()) {
            $branches = $branches->where('id', auth()->user()->currentBranch()->id);
        }

        /*$pdf = \PDF::loadView('tenant.income.index', [
            'branches' => $branches
        ]);

        return $pdf->download(uniqid('payments_', true) . '.pdf');*/

        return view('tenant.income.index', [
            'branches' => $branches
        ]);
    }
}
