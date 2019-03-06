<?php

namespace Logistics\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
use Illuminate\Support\Fluent;
use Logistics\DB\Tenant\Client;
use Logistics\DB\Tenant\Payment;
use Logistics\Traits\InvoiceList;
use Logistics\Exports\InvoicesExport;
use Illuminate\Support\Facades\Validator;
use Logistics\Http\Controllers\Controller;
use Logistics\Http\Requests\Tenant\InvoiceRequest;
use Logistics\Jobs\Tenant\SendInvoiceCreatedEmail;
use Logistics\Notifications\Tenant\InvoiceActivity;

class InvoiceController extends Controller
{
    use Tenant, InvoiceList;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        [$invoices, $searching, $branch] = $this->getInvoices($this->getTenant());

        $branches = $this->getBranches();

        if (!auth()->user()->isSuperAdmin()) {
            $branches = $branches->where('id', auth()->user()->currentBranch()->id);
        }

        return view('tenant.invoice.index', [
            'invoices' => $invoices ,
            'searching' => $searching,
            'branch' => $branch,
            'sign' => '$',
            'branches' => $branches,
        ]);
    }

    public function export()
    {
        [$invoices, $searching, $branch] = $this->getInvoices($this->getTenant());

        $data = [
            'invoices' => $invoices,
            'branch' => $branch,
            'exporting' => true,
            'sign' => '',
        ];

        if (request('pdf')) {
            // return view('tenant.export.invoices-pdf', $data);

            $pdf = \PDF::loadView('tenant.export.invoices-pdf', $data);

            return $pdf->download(uniqid('invoices_', true) . '.pdf');
        }
        
        return (new InvoicesExport)->download(uniqid('invoices_', true) . '.xlsx');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('tenant.invoice.create', [
            'clients' => (new Client())->getClientsByBranch(request('branch_id')),
            'product_types' => auth()->user()->currentBranch()->productTypes()->active()->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Logistics\Http\Requests\Tenant\InvoiceRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(InvoiceRequest $request)
    {
        $tenant = $this->getTenant();

        $invoice = new \Logistics\DB\Tenant\Invoice;

        if ($request->manual_id) {
            $invoice->manual_id = $request->manual_id;
        } else {
            $max = $invoice->where('tenant_id', $tenant->id)
                ->where('branch_id', $request->branch_id)
                ->max('manual_id');

            if (!$max) {
                $max = 0;
            }

            $invoice->manual_id = $max + 1;
        }

        $invoice->tenant_id = $tenant->id;
        $invoice->branch_id = $request->branch_id;
        $invoice->client_id = $request->client_id;
        $invoice->total = $request->total;
        $invoice->notes = $request->notes;
        $invoice->created_at = $request->created_at;

        $saved = $invoice->save();

        if ($saved) {
            $invoice = $invoice->fresh();

            foreach ($request->invoice_detail as $detail) {
                $detail = new Fluent($detail);
                $invoice->details()->create([
                    'qty' => $detail->qty,
                    'type' => $detail->type,
                    'description' => $detail->description,
                    'id_remote_store' => $detail->id_remote_store,
                    'total' => $detail->total,
                ]);
            }

            if ($request->amount_paid > 0) {
                $payment = $invoice->payments()->create([
                    'tenant_id' => $invoice->tenant_id,
                    'amount_paid' => $request->amount_paid,
                    'payment_method' => $request->payment_method,
                    'payment_ref' => $request->payment_ref,
                    'created_at' => $request->created_at,
                    'is_first' => true,
                ]);
            } else {
                $payment = new Payment;
            }

            $tenant->branches->where('id', $request->branch_id)->first()
                   ->notify(new InvoiceActivity($invoice, $payment->id, auth()->user()->full_name));

            if ($invoice->client->email !== $tenant->email_allowed_dup) {
                dispatch(new SendInvoiceCreatedEmail($tenant, $invoice));
            }

            return redirect()->route('tenant.invoice.edit', [$tenant->domain, $invoice->id, 'branch_id' => $request->branch_id, ])
                ->with('flash_success', __('The :what has been created.', ['what' => __('Invoice') ]));
        }

        return redirect()->route('tenant.warehouse.create', [$tenant->domain, 'branch_id' => $request->branch_id, ])
            ->withInput()
            ->with('flash_error', __('Error while trying to :action :what', [
                'action' => __('Save'),
                'what' => __('The warehouse'),
            ]));
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $domain
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($domain, $id)
    {
        $tenant = $this->getTenant();

        $invoice = $tenant->invoices()->with([
            'details' => function ($detail) {
                $detail->with('productType');
            },
            'creator',
            'editor',
            'branch',
            'warehouse',
            'payments' => function ($payment) {
                $payment->with(['creator']);
            },
            'client' => function ($client) {
                $client->with(['branch']);
            },
        ]);
        
        if (!auth()->user()->isSuperAdmin()) {
            //$invoice = $invoice->where('branch_id', auth()->user()->currentBranch()->id);
        }
        
        $invoice = $invoice->findOrFail($id);

        return view('tenant.invoice.show', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($domain, $id)
    {
        $tenant = $this->getTenant();
        $invoice = $tenant->invoices()->with(['details', 'creator', 'editor', 'client', 'branch', 'payments' => function ($payment) {
            $payment->with(['creator']);
        }]);

        if (!auth()->user()->isSuperAdmin()) {
            $invoice = $invoice->where('branch_id', auth()->user()->currentBranch()->id);
        }

        $invoice = $invoice->findOrFail($id);

        return view('tenant.invoice.edit', [
            'clients' => (new Client())->getClientsByBranch(request('branch_id')),
            'invoice' => $invoice,
            'payments' => $invoice->payments,
            'product_types' => $invoice->branch->productTypes()->active()->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Logistics\Http\Requests\Tenant\InvoiceRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(InvoiceRequest $request, $domain, $id)
    {
        $tenant = $this->getTenant();
        $invoice = $tenant->invoices()->with('details');

        if (!auth()->user()->isSuperAdmin()) {
            $invoice = $invoice->where('branch_id', auth()->user()->currentBranch()->id);
        }

        $invoice = $invoice->findOrFail($id);

        $payment = $invoice->payments->where('is_first', true)->first();

        $invoice->client_id = $request->client_id;
        $invoice->total = $request->total;
        $invoice->notes = $request->notes;
        $invoice->created_at = $request->created_at;
        $invoice->save();

        if ($invoice) {
            foreach ($request->invoice_detail as $detail) {
                $detail = new Fluent($detail);
                $invoice->details()->updateOrCreate(['id' => $detail->idid ], [
                    'qty' => $detail->qty,
                    'type' => $detail->type,
                    'description' => $detail->description,
                    'id_remote_store' => $detail->id_remote_store,
                    'total' => $detail->total,
                ]);
            }

            if ($request->amount_paid) {
                $payment->amount_paid = $request->amount_paid;
                $payment->payment_method = $request->payment_method;
                $payment->payment_ref = $request->payment_ref;
                $payment->save();
            }
            
            if ($invoice->client->email !== $tenant->email_allowed_dup) {
                dispatch(new SendInvoiceCreatedEmail($tenant, $invoice));
            }

            return redirect()->route('tenant.invoice.edit', [$tenant->domain, $invoice->id, 'branch_id' => $request->branch_id,])
                ->with('flash_success', __('The :what has been updated.', ['what' => __('Invoice') ]));
        }

        return redirect()->route('tenant.warehouse.edit', [$tenant->domain, $invoice->id, 'branch_id' => $request->branch_id, ])
            ->withInput()
            ->with('flash_error', __('Error while trying to :action :what', [
                'action' => __('Update'),
                'what' => __('The invoice'),
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

    public function invoiceDetTpl($tenant)
    {
        return response()->json([
            'view' => view('tenant.invoice.detail', [
                'product_types' => auth()->user()->currentBranch()->productTypes()->active()->get(),
            ])->render(),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function print($domain, $id)
    {
        $tenant = $this->getTenant();
        
        $invoice = $tenant->invoices()->with(['details' => function ($detail) {
            $detail->with('productType');
        }]);

        if (!auth()->user()->isSuperAdmin()) {
            $invoice = $invoice->where('branch_id', auth()->user()->currentBranch()->id);
        }

        $invoice = $invoice->findOrFail($id);

        $payments = $invoice->payments;

        $client = $invoice->client()->with('branch')->first();
        $branch = $client->branch;
        $box = "{$branch->code}{$client->manual_id_dsp}";

        $data = [
            'creatorName' => $invoice->creator ? $invoice->creator->full_name : null,
            'client' => $client,
            'box' => $box,
            'invoice' => $invoice,
            'tenant' => $tenant,
            'ibranch' => $invoice->branch,
            'amountPaid' => $payments->sum('amount_paid'),
        ];

        if (app()->environment('testing') || request('html')) {
            return view('tenant.invoice.printing', $data);
        } else {
            $pdf = \PDF::loadView('tenant.invoice.printing', $data);

            return $pdf->download(uniqid('invoice_', true) . '.pdf');
        }
    }

    public function resendInvoice($domain, $invoiceId)
    {
        $tenant = $this->getTenant();

        $invoice = $tenant->invoices()->with('details');

        if (!auth()->user()->isSuperAdmin()) {
            $invoice = $invoice->where('branch_id', auth()->user()->currentBranch()->id);
        }

        $invoice = $invoice->findOrFail($invoiceId);

        if (!$invoice) {
            return response()->json(['error' => true, 'msg' => __('Not Found.'), ], 404);
        }

        if ($invoice->client->email !== $tenant->email_allowed_dup) {
            dispatch(new SendInvoiceCreatedEmail($tenant, $invoice));
        }

        return response()->json(['error' => false, 'msg' => __('Success'), ]);
    }

    public function penalize(Request $request)
    {
        $validator = $this->validateFine($request);

        if ($validator->fails()) {
            return response()->json([
                'msg' => $validator->errors()->first(),
                'error' => true,
            ], 500);
        }

        $tenant = $this->getTenant();
        $invoice = $tenant->invoices()->where('is_paid', false)->with(['payments']);

        if (!auth()->user()->isSuperAdmin()) {
            $invoice = $invoice->where('branch_id', auth()->user()->currentBranch()->id);
        }

        $invoice = $invoice->find($request->invoice_id);

        if (!$invoice) {
            return response()->json([
                'msg' => __('Not Found.'),
                'error' => true,
            ], 404);
        }

        $fined = $invoice->update([
            'total' => $request->fine_total + $invoice->total,
            'fine_total' => $request->fine_total,
            'fine_ref' => $request->fine_ref,
        ]);
        
        if ($fined) {
            $totalPaid = $invoice->fresh()->payments->fresh()->sum('amount_paid');
            $pending = $invoice->total - $totalPaid;

            return response()->json([
                'error' => false,
                'msg' => __('Success'),
                'total' => number_format($invoice->total, 2),
                'totalPaid' => number_format($totalPaid, 2),
                'pending' => number_format($pending, 2),
            ], 200);
        }
            
        return response()->json([
            'error' => true,
            'msg' => __('Error'),
        ], 500);
    }

    public function inactive(Request $request)
    {
        $tenant = $this->getTenant();
        $invoice = $tenant->invoices();

        if (!auth()->user()->isSuperAdmin()) {
            $invoice = $invoice->where('branch_id', auth()->user()->currentBranch()->id);
        }

        $invoice = $invoice->find($request->invoice_id);

        if (!$invoice) {
            return response()->json([
                'msg' => __('Not Found.'),
                'error' => true,
            ], 404);
        }

        if ($request->status) {
            $inactive = $invoice->update([
                'status' => $request->status,
                'notes'  => $invoice->notes . PHP_EOL . $request->notes,
            ]);
        } else {
            $inactive = $invoice->update([
                'status' => 'I',
            ]);
        }
        
        if ($inactive) {
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

    private function validateFine($request, $extraRules = [])
    {
        $rules = [
            'invoice_id' => 'required',
            'fine_total' => 'required|numeric',
            'fine_ref' => 'required|between:3,255',
        ];

        return Validator::make($request->all(), array_merge($rules, $extraRules));
    }
}
