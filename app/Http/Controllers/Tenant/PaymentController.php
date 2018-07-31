<?php

namespace Logistics\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Logistics\Http\Controllers\Controller;
use Logistics\Notifications\Tenant\PaymentActivity;

class PaymentController extends Controller
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
        $branch = auth()->user()->currentBranch();

        $payments = DB::table('payments')
            ->where('tenant_id', $tenant->id)
            ->join('invoices', 'payments.invoice_id', '=', 'invoices.id')
            ->join('clients', function ($join) {
                if ($clientId = request('client_id')) {
                    $join->on('invoices.client_id', '=', 'clients.id')
                         ->where('clients.id', '=', $clientId);
                } else {
                }
            })
            ->join('boxes', function ($join) use ($branch) {
                $join->on('clients.id', '=', 'boxes.client_id')
                    ->where('boxes.branch_id', '=', $branch->id)
                    ->where('boxes.status', '=', 'A');
            })
            ;

        $sql = "select p.*, i.total, i.is_paid,nconcat(c.first_name, ' ', c.last_name) as full_name, c.org_name, b.branch_code";
        $sql .= " from payments p";
        $sql .= " inner join invoices as i on p.invoice_id = i.id inner join clients as c on i.client_id = c.id";
        $sql .= " inner join boxes as b on b.client_id = b.id and b.status = 'A' and b.branch_id = {$branch->id} where 1 = 1";

        if ($from = request('from') && $to = request('to')) {
            $sql .= " and date(p.created_at) between ? and ?";
        }

        if ($clientId = request('client_id')) {
            $sql .= " and c.id = ? ";
        }


        dd($sql);

        $payments = $tenant->payments()->with(['invoice' => function ($invoice) {
            $invoice->select(['client_id', 'id', 'total', 'is_paid'])
                ->with(['client' => function ($client) {
                    $client->select(['id', 'first_name', 'last_name', 'org_name'])
                        ->with(['boxes' => function ($box) {
                            $box->select(['id', 'branch_id', 'branch_code', 'client_id'])
                                ->where('status', 'A');
                        }]);
                }]);
        }]);

        return view('tenant.payment.index', [
            'payments' => $payments->paginate(20),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($domain, $invoiceId)
    {
        $tenant = $this->getTenant();
        $invoice = $tenant->invoices()->with(['details', 'payments'])->find($invoiceId);

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
        $invoice = $tenant->invoices()->with(['payments'])->find($request->invoice_id);

        if (!$invoice) {
            return response()->json([
                'msg' => __('Not Found.'),
                'error' => true,
            ], 404);
        }

        $pending = $invoice->total - $invoice->payments->sum('amount_paid');

        if ($request->amount_paid > $pending) {
            return response()->json([
                'msg' => __('validation.lte.numeric', ['attribute' => __('Amount paid'), 'value' => number_format($pending, 2) ]),
                'error' => true,
            ], 500);
        }

        $payment = $invoice->payments()->create([
            'is_first' => false,
            'tenant_id' => $invoice->tenant_id,
            'amount_paid' => $request->amount_paid,
            'payment_method' => $request->payment_method,
            'payment_ref' => $request->payment_ref,
        ]);

        if ($payment) {
            $invoice->branch->notify(new PaymentActivity($payment, $invoice->client_id, $tenant->lang));

            $pending = $invoice->total - $invoice->fresh()->payments->fresh()->sum('amount_paid');

            if (!$pending) {
                $invoice->update(['is_paid' => true]);
            }

            return response()->json([
                'error' => false,
                'msg' => __('Success'),
                'pending' => $pending,
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
