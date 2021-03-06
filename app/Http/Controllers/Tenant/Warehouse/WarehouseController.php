<?php

namespace Logistics\Http\Controllers\Tenant\Warehouse;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
use Logistics\DB\Tenant\Client;
use Logistics\DB\Tenant\Invoice;
use Logistics\Traits\WarehouseList;
use Logistics\Http\Controllers\Controller;
use Logistics\Http\Requests\Tenant\WarehouseRequest;
use Logistics\Jobs\Tenant\SendWarehouseCreatedEmail;
use Logistics\Jobs\Tenant\SendWarehouseReceiptEmail;

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
        [$warehouses, $searching, $branch] = $this->listWarehouses($this->getTenant());

        $branches = $this->getBranches();
        $user = auth()->user();

        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->isWarehouse()) {
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

        [$year, $month, $day]  = array_map('intval', explode('-', request('created_at', date('Y-m-d'))));

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
        $wh->created_at = Carbon::create($year, $month, $day);

        $saved = $wh->save();

        if ($saved) {
            $wh = $wh->fresh();
            $details = $request->invoice_detail ? : [];
            
            if (count($details)) {
                $wh->genInvoice($request, $tenant);
            } else {
                dispatch(new SendWarehouseCreatedEmail($tenant, $wh));
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
    public function show($tenant, $id)
    {
        $tenant = $this->getTenant();
        $user = auth()->user();
        $warehouse = $tenant->warehouses();

        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->isWarehouse()) {
            $warehouse = $warehouse->where('branch_to', $user->currentBranch()->id);
        }

        if ($user->isClient()) {
            $warehouse = $warehouse->where('client_id', $user->client_id);
        }

        $warehouse = $warehouse->with(['editor', 'creator'])
            ->where('id', $id)->firstOrFail();

        $invoice = $warehouse->invoice()->with(['creator', 'payments' => function ($payment) {
            $payment->active()->with('creator');
        }])->first();

        if (!$invoice) {
            $invoice = new Invoice;
        }

        return view('tenant.warehouse.show', [
            'warehouse' => $warehouse,
            'branches' => $this->getBranches()->where('id', $warehouse->branch_to),
            'userBranches' => $this->getBranches()->where('id', $warehouse->branch_from),
            'mailers' => $this->mailers()->where('id', $warehouse->mailer_id),
            'invoice' => $invoice,
            'clients' => ((new Client)->getClientsByBranch($warehouse->branch_to))->where('id', $warehouse->client_id),
        ]);
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

        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->isWarehouse()) {
            $warehouse = $warehouse->where('branch_to', $user->currentBranch()->id);
        }

        $warehouse = $warehouse->with(['editor', 'creator'])
            ->where('id', $id)->firstOrFail();

        $invoice = $warehouse->invoice()->with(['creator', 'payments' => function ($payment) {
            $payment->active()->with('creator');
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

        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->isWarehouse()) {
            $warehouse = $warehouse->where('branch_to', $user->currentBranch()->id);
        }

        $warehouse = $warehouse->where('id', $id)->firstOrFail();

        [$year, $month, $day]  = array_map('intval', explode('-', request('created_at', date('Y-m-d'))));

        if ($request->amount_paid > 0) {
            abort_if(
                $request->amount_paid > $request->total,
                500,
                __('validation.lte.numeric', [
                'attribute' => __('Amount paid'),
                'value' => number_format($request->total, 2)
                ])
            );
        }


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
        
        if ($warehouse->created_at->format('Y-m-d') != request('created_at')) {
            $warehouse->created_at = Carbon::create($year, $month, $day);
        }
        $warehouse->force_updated_at = time();
            
        $updated = $warehouse->save();
        
        if ($updated) {
            $details = $request->invoice_detail ? : [];

            if (count($details)) {
                $warehouse->genInvoice($request, $tenant);
            } else {
                dispatch(new SendWarehouseCreatedEmail($tenant, $warehouse));
            }

            return redirect()->route('tenant.warehouse.edit', [$tenant->domain, $warehouse->id])
                ->with('flash_success', __('The :what has been updated.', ['what' => __('Warehouse')]));
        }

        return redirect()->route('tenant.warehouse.edit', [$tenant->domain, $warehouse->id])
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
                $invoice->notes = $request->notes . PHP_EOL . $invoice->notes;
                $invoice->save();

                $invoice->payments()->update(['status' => $request->status, 'updated_by_code' => auth()->user()->id, ]);
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

        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->isWarehouse()) {
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
        
        return view('tenant.warehouse.sticker', $data);

        if (app()->environment('testing')) {
            return view('tenant.warehouse.sticker', $data);
        } else {
            $pdf = \PDF::loadView('tenant.warehouse.sticker', $data);
                
            return $pdf->download(uniqid('sticker_', true) . '.pdf');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $tenant
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function receipt($tenant, $id)
    {
        $tenant = $this->getTenant();
        $warehouse = $tenant->warehouses();
        $user = auth()->user();
        $lang = $tenant->lang ?: localization()->getCurrentLocale();

        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->isWarehouse()) {
            $warehouse = $warehouse->where('branch_to', $user->currentBranch()->id);
        }

        $warehouse = $warehouse->where('id', $id)->firstOrFail();

        $data = [
            'warehouse' => $warehouse,
            'branchTo' => $tenant->branches()->select(['tenant_id', 'id', 'name', 'initial', 'address', 'telephones'])->find($warehouse->branch_to),
            'mailer' => $tenant->mailers()->select(['tenant_id', 'id', 'name'])->find($warehouse->mailer_id),
            'client' => $tenant->clients()
                ->with('branch')
                ->select(['tenant_id', 'id', 'first_name', 'last_name', 'address', 'email', 'telephones', 'branch_id', 'manual_id'])
                ->find($warehouse->client_id),
            'invoice' => $warehouse->invoice()
                ->with('details')->first(),
            'lang' => $lang,
        ];

        if (request('__print_it') == '1') {
            $pdf = \PDF::loadView('tenant.warehouse.receipt', $data);

            return $pdf->download(uniqid('whreceipt_', true) . '.pdf');
        }

        if (request('__send_it') == '1') {
            dispatch(new SendWarehouseReceiptEmail($tenant, $data));

            return response()->json(['error' => false, 'msg' => __('Success'), ]);
        }

        return view('tenant.warehouse.receipt', $data);
    }
}
