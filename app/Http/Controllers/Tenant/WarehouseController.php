<?php

namespace Logistics\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
use Logistics\DB\Tenant\Client;
use Logistics\DB\Tenant\Invoice;
use Logistics\Traits\WarehouseList;
use Logistics\Exports\WarehousesExport;
use Logistics\Http\Controllers\Controller;
use Logistics\Http\Requests\Tenant\WarehouseRequest;

class WarehouseController extends Controller
{
    use Tenant, WarehouseList;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        [$warehouses, $searching, $branch] = $this->getWarehouses($this->getTenant());

        return view('tenant.warehouse.index', [
            'warehouses' => $warehouses,
            'searching' => $searching,
            'branch' => $branch,
            'sign' => '$',
            'branches' => $this->branches(),
        ]);
    }

    public function export()
    {
        [$warehouses, $searching, $branch] = $this->getWarehouses($this->getTenant());

        $data = [
            'warehouses' => $warehouses,
            'branch' => $branch,
            'exporting' => true,
            'sign' => '',
        ];

        if (request('pdf')) {
            // return view('tenant.export.warehouses-pdf', $data);

            $pdf = \PDF::loadView('tenant.export.warehouses-pdf', $data);

            return $pdf->download(uniqid('warehouses_', true) . '.pdf');
        }
        
        return (new WarehousesExport)->download(uniqid('warehouses_', true) . '.xlsx');
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
            'invoice' => new Invoice,
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
            'branch_to' => $request->branch_to,
            'branch_from' => $request->branch_from,
            'client_id' => $request->client_id,
            'mailer_id' => $request->mailer_id ?: 0,
            'trackings' => $request->trackings,
            'reference' => $request->reference,
            'type' => $request->type,
            'qty' => $request->qty ?: 0,
        ]);

        if ($warehouse) {
            $details = $request->invoice_detail ? : [];
            if (count($details)) {
                $warehouse->genInvoice($request);
            }
            
            return redirect()->route('tenant.warehouse.edit', [$tenant->domain, $warehouse->id])
                ->with('flash_success', __('The :what has been created.', ['what' => __('Warehouse') ]));
        }

        return redirect()->route('tenant.client.create', $tenant->domain)
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
    public function edit($tenant, $id)
    {
        $tenant = $this->getTenant();

        $warehouse = $tenant->warehouses()->where('id', $id)->firstOrFail();

        return view('tenant.warehouse.edit', [
            'warehouse' => $warehouse,
            'branches' => $this->branches(),
            'userBranches' => $this->branches()->whereIn('id', auth()->user()->branchesForInvoice->pluck('id')->toArray()),
            'mailers' => $this->mailers(),
            'invoice' => $warehouse->invoice ?: new Invoice,
            'clients' => (new Client)->getClientsByBranch($warehouse->branch_to),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Logistics\Http\Requests\Tenant\WarehouseRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(WarehouseRequest $request, $tenant, $id)
    {
        $tenant = $this->getTenant();
        $warehouse = $tenant->warehouses()->where('id', $id)->firstOrFail();

        $warehouse->branch_to = $request->branch_to;
        $warehouse->branch_from = $request->branch_from;
        $warehouse->mailer_id = $request->mailer_id;
        $warehouse->client_id = $request->client_id;
        $warehouse->trackings = $request->trackings;
        $warehouse->reference = $request->reference;
        $warehouse->qty = $request->qty;
        $warehouse->type = $request->type;
            
        $updated = $warehouse->save();
        
        if ($updated) {
            if ($request->client_id) {
                $warehouse->genInvoice($request);
            }

            return redirect()->route('tenant.warehouse.edit', [$tenant->domain, $warehouse->id])
                ->with('flash_success', __('The :what has been updated.', ['what' => __('Warehouse')]));
        }

        return redirect()->route('tenant.warehouse.create', $tenant->domain)
            ->withInput()
            ->with('flash_error', __('Error while trying to :action :what', [
                'action' => __('Update'),
                'what' => __('The warehouse'),
            ]));
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

    public function invoiceTpl($tenant)
    {
        return response()->json([
            'view' => view('tenant.warehouse.invoice', [
                'invoice' => new Invoice(),
            ])->render(),
        ]);
    }

    public function invoiceDetTpl($tenant)
    {
        return response()->json([
            'view' => view('tenant.warehouse.invoice-detail')->render(),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function sticker($tenant, $id)
    {
        $tenant = $this->getTenant();
        $warehouse = $tenant->warehouses()->where('id', $id)->firstOrFail();

        $data = [
            'iata' => $tenant->country->iata,
            'warehouse' => $warehouse,
            'mailer' => $tenant->mailers()->select(['tenant_id', 'id', 'name'])->find($warehouse->mailer_id),
            'branchTo' => $tenant->branches()->select(['tenant_id', 'id', 'name', 'address'])->find($warehouse->branch_to),
            'client' => $tenant->clients()->select(['tenant_id', 'id', 'first_name', 'last_name', 'address'])
                ->with(['boxes' => function ($query) use ($warehouse) {
                    $query->select(['id', 'client_id', 'branch_code', 'branch_id'])
                        ->where('status', 'A')->where('branch_id', $warehouse->branch_to);
                }])
                ->find($warehouse->client_id),
            'invoice' => $warehouse->invoice()->select(['id', 'warehouse_id', 'total'])
                ->with(['details' => function ($query) {
                }])->first(),
        ];

        if (app()->environment('testing')) {
            return view('tenant.warehouse.sticker', $data);
        } else {
            $pdf = \PDF::loadView('tenant.warehouse.sticker', $data);
                
            return $pdf->download(uniqid('sticker_', true) . '.pdf');
        }
    }
}
