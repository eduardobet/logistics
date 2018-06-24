<?php

namespace Logistics\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
use Logistics\Http\Controllers\Controller;
use Logistics\Http\Requests\Tenant\WarehouseRequest;

class WarehouseController extends Controller
{
    use Tenant;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('tenant.warehouse.create', [
            'branches' => $this->branches(),
            'userBranches' => $this->branches()->whereIn('id', auth()->user()->branchesForInvoice->pluck('id')->toArray()),
            'mailers' => $this->mailers(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Logistics\Http\Requests\Tenant\WarehouseRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(WarehouseRequest $request)
    {
        $tenant = $this->getTenant();

        $warehouse = $tenant->warehouses()->create([
            'branch_to_issue' => $request->branch_to_issue,
            'mailer_id' => $request->mailer_id,
            'client_id' => $request->client_id,
            'trackings' => $request->trackings,
            'reference' => $request->reference,
            'qty' => $request->qty,
        ]);

        if ($warehouse) {
            return redirect()->route('tenant.warehouse.edit', $warehouse->id)
                ->with('flash_success', __('The :what has been created.', ['what' => __('Warehouse') ]));
        }

        return redirect()->route('tenant.client.create')
            ->withInput()
            ->with('flash_error', __('Error while trying to :action :what', [
                'action' => __('Save'),
                'what' => __('The warehouse'),
            ]));
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
