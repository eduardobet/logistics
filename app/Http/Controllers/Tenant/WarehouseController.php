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

        $branches = $this->getBranches();
        $user = auth()->user();

        if (!$user->isSuperAdmin() && !$user->isWarehouse()) {
            $branches = $branches->where('id', $user->currentBranch()->id);
        }

        return view('tenant.warehouse.index', [
            'warehouses' => $warehouses,
            'searching' => $searching,
            'branch' => $branch,
            'sign' => '$',
            'branches' => $branches,
        ]);
    }

    public function export()
    {
        [$warehouses, $branch] = $this->getWarehouses($this->getTenant());

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
            'branches' => $this->getBranches(),
            'userBranches' => $this->getBranches()->whereIn('id', auth()->user()->branchesForInvoice->pluck('id')->toArray()),
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

        $wh = new \Logistics\DB\Tenant\Warehouse;

        if ($request->manual_id) {
            $wh->manual_id = $request->manual_id;
        } else {
            $max = $wh->where('tenant_id', $tenant->id)
                //->where('branch_to', $request->branch_to)
                ->max('manual_id');

            if (!$max) {
                $max = 0;
            }

            $wh->manual_id = $max + 1;
        }

        $wh->tenant_id = $tenant->id;
        $wh->branch_to = $request->branch_to;
        $wh->branch_from = $request->branch_from;
        $wh->client_id = $request->client_id ?: 0;
        $wh->mailer_id = $request->mailer_id ?: 0;
        $wh->trackings = $request->trackings;
        $wh->reference = $request->reference;
        $wh->type = $request->type;
        $wh->qty = $request->qty ?: 0;
        $wh->tot_packages = $request->tot_packages ?: 0;
        $wh->tot_weight = $request->tot_weight ?: 0;
        $wh->created_at = new \Carbon\Carbon($request->created_at);

        $saved = $wh->save();

        if ($saved) {
            $wh = $wh->fresh();
            $details = $request->invoice_detail ? : [];
            
            if (count($details)) {
                $wh->genInvoice($request, $tenant);
            }
            
            return redirect()->route('tenant.warehouse.edit', [$tenant->domain, $wh->id])
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
        $user = auth()->user();
        $warehouse = $tenant->warehouses();

        if (!$user->isSuperAdmin() && !$user->isWarehouse()) {
            $warehouse = $warehouse->where('branch_to', $user->currentBranch()->id);
        }

        $warehouse = $warehouse->with(['editor', 'creator'])
            ->where('id', $id)->firstOrFail();

        $invoice = $warehouse->invoice()->with(['creator', 'payments' => function ($payment) {
            $payment->with('creator');
        }])->first();

        if (!$invoice) {
            $invoice = new Invoice;
        }

        return view('tenant.warehouse.edit', [
            'warehouse' => $warehouse,
            'branches' => $this->getBranches(),
            'userBranches' => $this->getBranches()->whereIn('id', auth()->user()->branchesForInvoice->pluck('id')->toArray()),
            'mailers' => $this->mailers(),
            'invoice' => $invoice,
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
        $user = auth()->user();
        $warehouse = $tenant->warehouses();

        if (!$user->isSuperAdmin() && !$user->isWarehouse()) {
            $warehouse = $warehouse->where('branch_to', $user->currentBranch()->id);
        }

        $warehouse = $warehouse->where('id', $id)->firstOrFail();

        $warehouse->branch_to = $request->branch_to;
        $warehouse->branch_from = $request->branch_from;
        $warehouse->mailer_id = $request->mailer_id ?: 0;
        $warehouse->client_id = $request->client_id ?: 0;
        $warehouse->trackings = $request->trackings;
        $warehouse->reference = $request->reference;
        $warehouse->qty = $request->qty ?: 0;
        $warehouse->type = $request->type;
        $warehouse->tot_packages = $request->tot_packages ?: 0;
        $warehouse->tot_weight = $request->tot_weight ?: 0;
        $warehouse->force_updated_at = time();
            
        $updated = $warehouse->save();
        
        if ($updated) {
            $details = $request->invoice_detail ? : [];

            if (count($details)) {
                $warehouse->genInvoice($request, $tenant);
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

    public function toggle(Request $request)
    {
        $tenant = $this->getTenant();
        $wh = $tenant->warehouses()->find($request->warehouse_id);

        if (!$wh) {
            return response()->json([
                'msg' => __('Not Found.'),
                'error' => true,
            ], 404);
        }

        $status = $wh->update([
            'status' => $request->status,
        ]);

        if ($status) {
            $invoice = $wh->invoice;
            
            if ($invoice) {
                $invoice->status = $request->status;
                $invoice->notes = $invoice->notes . PHP_EOL . $request->notes;
                $invoice->save();
            }

            return response()->json([
                'error' => false,
                'msg' => __('Success'),
            ], 200);
        }

        return response()->json([
            'error' => true,
            'msg' => __('Error'),
        ], 500);
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
        $warehouse = $tenant->warehouses();
        $user = auth()->user();

        if (!$user->isSuperAdmin() && !$user->isWarehouse()) {
            $warehouse = $warehouse->where('branch_to', $user->currentBranch()->id);
        }

        $warehouse = $warehouse->where('id', $id)->firstOrFail();

        $data = [
            'iata' => $tenant->country->iata,
            'warehouse' => $warehouse,
            'mailer' => $tenant->mailers()->select(['tenant_id', 'id', 'name'])->find($warehouse->mailer_id),
            'branchTo' => $tenant->branches()->select(['tenant_id', 'id', 'name', 'address'])->find($warehouse->branch_to),
            'client' => $tenant->clients()
                ->with('branch')
                ->select(['tenant_id', 'id', 'first_name', 'last_name', 'address', 'branch_id', 'manual_id'])
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
