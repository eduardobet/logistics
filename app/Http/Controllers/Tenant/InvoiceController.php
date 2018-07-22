<?php

namespace Logistics\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
use Illuminate\Support\Fluent;
use Logistics\DB\Tenant\Client;
use Logistics\DB\Tenant\Payment;
use Illuminate\Support\Facades\Mail;
use Logistics\Mail\Tenant\InvoiceCreated;
use Logistics\Http\Controllers\Controller;
use Logistics\Http\Requests\Tenant\InvoiceRequest;
use Logistics\Notifications\Tenant\InvoiceActivity;

class InvoiceController extends Controller
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

        return view('tenant.invoice.index', [
            'invoices' => $tenant->invoices()->with('client')->paginate(20),
        ]);
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

        $invoice = $tenant->invoices()->create([
            'branch_id' => $request->branch_id,
            'client_id' => $request->client_id,
            'total' => $request->total,
        ]);

        if ($invoice) {
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
                    'is_first' => true,
                ]);
            } else {
                $payment = new Payment;
            }

            $tenant->branches->where('id', $request->branch_id)->first()
                   ->notify(new InvoiceActivity($invoice, $payment->id));

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
    public function edit($domain, $id)
    {
        $tenant = $this->getTenant();
        $invoice = $tenant->invoices()->with('details')->findOrFail($id);

        return view('tenant.invoice.edit', [
            'clients' => (new Client())->getClientsByBranch(request('branch_id')),
            'invoice' => $invoice,
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
        $invoice = $tenant->invoices()->with('details')->findOrFail($id);

        $payment = $invoice->payments->where('is_first', true)->first();

        $invoice->client_id = $request->client_id;
        $invoice->total = $request->total;
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

            $payment->amount_paid = $request->amount_paid;
            $payment->payment_method = $request->payment_method;
            $payment->payment_ref = $request->payment_ref;
            $payment->save();

            Mail::to($invoice->client)->send(new InvoiceCreated($invoice, $tenant->lang));

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
            'view' => view('tenant.invoice.detail')->render(),
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
        
        $invoice = $tenant->invoices()->with('details')->findOrFail($id);
        $payments = $invoice->payments;

        $client = $invoice->client;
        $box = $client->boxes()->active()->get()->first();
        $box = "{$box->branch_code}{$client->id}";

        $data = [
            'creatorName' => $invoice->creator ? $invoice->creator->full_name : null,
            'client' => $client,
            'box' => $box,
            'invoice' => $invoice,
            'tenant' => $tenant,
            'ibranch' => $invoice->branch,
            'amountPaid' => $payments->sum('amount_paid')
        ];

        if (app()->environment('testing')) {
            return view('tenant.invoice.printing', $data);
        } else {
            $pdf = \PDF::loadView('tenant.invoice.printing', $data);

            return $pdf->download('invoice.pdf');
        }
    }

    public function resendInvoice($domain, $invoiceId)
    {
        $tenant = $this->getTenant();
        $invoice = $tenant->invoices()->with('details')->findOrFail($invoiceId);


        if (!$invoice) {
            return response()->json(['error' => true, 'msg' => __('Not Found.'), ], 404);
        }

        Mail::to($invoice->client)->send(new InvoiceCreated($invoice, $tenant->lang));

        return response()->json(['error' => false, 'msg' => __('Success'), ]);
    }
}
