<?php

namespace Logistics\Http\Controllers\Tenant;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
use Barryvdh\Snappy\Facades\SnappyPdf;
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
        $cBranch = auth()->user()->currentBranch();

        $from =  Carbon::parse(request('from', date('Y-m-d')))->startOfDay();
        $to =  Carbon::parse(request('to', date('Y-m-d')))->endOfDay();
        $paymentsByType = $tenant->payments()
            ->with(['invoice' => function ($invoice) {
                $invoice->with('warehouse');
            }])
            ->whereHas('invoice', function ($invoice) use ($cBranch) {
                $invoice->active()->paid()->where('branch_id', request('branch_id', $cBranch->id));
            });

            
        $paymentsByType = $paymentsByType->whereBetween('created_at', [$from, $to]);
        
        $invoices = $tenant->invoices()->active()->whereBetween('created_at', [$from, $to])
            ->where('branch_id', request('branch_id', $cBranch->id))
            ->with(['details' => function ($detail) {
                $detail->with('productType');
            }])
            ->get();

        if ($pmethod = request('type')) {
            $paymentsByType = $paymentsByType->where('payment_method', $pmethod);
        }
        
        $details = $invoices->where('is_paid', true)->pluck('details')->flatten();

        $commissions = $details->filter(function ($value) {
            return $value->productType && $value->productType->is_commission == true;
        });

        $recas = $tenant->cargoEntries()->whereBetween('created_at', [$from, $to])
            ->where('branch_id', request('branch_id', $cBranch->id))
            ->where('weight', '>', 0)
            ->get();


        $incomes = $paymentsByType->get();

        $data = [
            'branches' => $branches,
            'payments_by_type' => $incomes,
            'tot_charged' => $invoices->sum('total'),
            'tot_income' => $incomes->sum('amount_paid'),
            'tot_in_cash' => $incomes->where('payment_method', 1)->sum('amount_paid'),
            'tot_in_wire' => $incomes->where('payment_method', 2)->sum('amount_paid'),
            'tot_in_check' => $incomes->where('payment_method', 3)->sum('amount_paid'),
            'tot_commission' => $commissions->sum('total'),
            'tot_fine' => $invoices->sum('fine_total'),
            'recas' => $recas,
            'printing' => 1,
        ];

        if (request('_print_it')) {
            // https://gist.github.com/srmds/2507aa3bcdb464085413c650fe42e31d#wkhtmltopdf-0125---ubuntu-1804-x64

            $pdf = SnappyPDF::loadView('tenant.income._index', $data);
            return $pdf->download(uniqid('invoice_', true) . '.pdf');
        }

        return view('tenant.income.index', $data);
    }
}
