<?php

namespace Logistics\Http\Controllers\Tenant\Employee;

use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
use Logistics\Http\Controllers\Controller;

class DashboardController extends Controller
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

        $type = auth()->user()->type;
        $prefix = 'employee';

        if ($type == 'A') {
            $prefix = 'admin';
        }

        $branch = auth()->user()->currentBranch();

        return view("tenant.employee.dashboard", [
            'tot_warehouses' => $this->warehouses()->where('branch_to', $branch->id)->count(),
            'tot_clients' => $this->getClients()->where('branch_id', $branch->id)->count(),
            'tot_invoices' => $this->invoices()->where('branch_id', $branch->id)->count(),
            'last_5_clients' => $this->getClients()->where('branch_id', $branch->id)->sortByDesc('created_at')->take(5),
            'today_earnings' => $tenant->payments()->whereDate('created_at', '=', date('Y-m-d'))->get()->sum('amount_paid')
        ]);
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
