<?php

namespace Logistics\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
use Illuminate\Support\Carbon;
use Logistics\DB\Tenant\Client;
use Logistics\Traits\PaymentList;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Logistics\Exports\PaymentsExport;
use Illuminate\Support\Facades\Validator;
use Logistics\Http\Controllers\Controller;
use Logistics\Jobs\Tenant\SendPaymentCreatedEmail;
use Logistics\Notifications\Tenant\PaymentActivity;

class PaymentController extends Controller
{
    use Tenant, PaymentList;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        [$payments, $searching, $branch] = $this->getPayments($this->getTenant());
        $user = auth()->user();
        $branches = $this->getBranches();

        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->isWarehouse()) {
            $branches = $branches->where('id', $user->currentBranch()->id);
        }

        return view('tenant.payment.index', [
            'payments' => $payments ,
            'searching' => $searching,
            'branch' => $branch,
            'sign' => '$',
            'branches' => $branches,
        ]);
    }

    public function export()
    {
        [$payments, $searching, $branch] = $this->getPayments($this->getTenant());

        $data = [
            'payments' => $payments,
            'branch' => $branch,
            'exporting' => true,
            'sign' => '',
        ];

        if (request('pdf')) {
            // return view('tenant.export.payments-pdf', $data);

            $pdf = \PDF::loadView('tenant.export.payments-pdf', $data);

            return $pdf->download(uniqid('payments_', true) . '.pdf');
        }
        
        return (new PaymentsExport)->download(uniqid('payments_', true) . '.xlsx');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($domain, $invoiceId)
    {
        $tenant = $this->getTenant();
        $invoice = $tenant->invoices()->with(['details', 'payments']);
        $user = auth()->user();

        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->isWarehouse()) {
            $invoice = $invoice->where('branch_id', $user->currentBranch()->id);
        }

        $invoice = $invoice->find($invoiceId);

        if (!$invoice) {
            return response()->json([
                'msg' => __('Not Found.'),
                'error' => true,
            ], 404);
        }

        return response()->json([
            'view' => view('tenant.payment.create', ['invoice' => $invoice, 'payments' => $invoice->payments, ])->render(),
            'error' => false,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->validates($request);

        if ($validator->fails()) {
            return response()->json([
                'msg' => $validator->errors()->first(),
                'error' => true,
            ], 500);
        }

        $tenant = $this->getTenant();
        $invoice = $tenant->invoices()->with(['payments', 'client']);
        $user = auth()->user();

        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->isWarehouse()) {
            $invoice = $invoice->where('branch_id', $user->currentBranch()->id);
        }

        $invoice = $invoice->find($request->invoice_id);


        if (!$invoice) {
            return response()->json([
                'msg' => __('Not Found.'),
                'error' => true,
            ], 404);
        }

        $totalPaid  = $invoice->payments->sum('amount_paid');

        if ($invoice->warehouse_id && $request->amount_paid < ($invoice->total - $totalPaid)) {
            return response()->json([
                'msg' => __('The invoice of a warehouse cannot be partially paid.'),
                'error' => true,
            ], 500);
        }

        $pending = $invoice->total - $totalPaid;

        if ($request->amount_paid > $pending) {
            return response()->json([
                'msg' => __('validation.lte.numeric', ['attribute' => __('Amount paid'), 'value' => number_format($pending, 2) ]),
                'error' => true,
            ], 500);
        }

        [$year, $month, $day]  = array_map('intval', explode('-', request('created_at', date('Y-m-d'))));

        $payment = $invoice->payments()->create([
            'is_first' => false,
            'tenant_id' => $invoice->tenant_id,
            'amount_paid' => $request->amount_paid,
            'payment_method' => $request->payment_method,
            'payment_ref' => $request->payment_ref,
            'created_at' => Carbon::create($year, $month, $day),
        ]);

        if ($payment) {
            $invoice->branch->notify(new PaymentActivity($payment, $invoice->client_id, $tenant->lang, auth()->user()->full_name, $invoice->manual_id_dsp));

            $totalPaid = $invoice->fresh()->payments->fresh()->sum('amount_paid');
            $pending = $invoice->total - $totalPaid;

            if (!$pending) {
                $invoice->update(['is_paid' => true]);
            }
            if ($invoice->client->email !== $tenant->email_allowed_dup) {
                dispatch(new SendPaymentCreatedEmail($tenant, $invoice, $payment));
            }

            return response()->json([
                'error' => false,
                'msg' => __('Success'),
                'pending' => number_format($pending, 2),
                'totalPaid' => number_format($totalPaid, 2),
            ], 200);
        }

        return response()->json([
            'error' => true,
            'msg' => __('Error'),
        ], 500);
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

        $payment = $tenant->payments()
            ->with([
                'creator',
                'invoice' => function ($invoice) {
                    $invoice->with('branch');
                }
            ])
            ->findOrFail($id);

        return view('tenant.payment.show', [
            'payment' => $payment
        ]);
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
        $tenant = $this->getTenant();

        $payment = $tenant->payments()->find($request->payment_id);

        if (! $payment) {
            return response()->json([
                'msg' => __('Not Found.'),
                'error' => true,
            ], 404);
        }

        $payment->payment_method = $request->payment_method;
        $updated = $payment->save();

        if ($updated) {
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

    private function validates($request, $extraRules = [])
    {
        $rules = [
            'invoice_id' => 'required',
            'amount_paid' => 'required|numeric',
            'payment_method' => 'required|integer',
            'payment_ref' => 'required|between:3,255',
        ];

        return Validator::make($request->all(), array_merge($rules, $extraRules));
    }
}
