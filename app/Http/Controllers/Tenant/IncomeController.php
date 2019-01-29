<?php

namespace Logistics\Http\Controllers\Tenant;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
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
            ->with('invoice')
            ->whereHas('invoice', function ($invoice) use ($cBranch) {
                $invoice->active()->paid()->where('branch_id', request('branch_id', $cBranch->id));
            });

            
        $paymentsByType = $paymentsByType->whereBetween('created_at', [$from, $to]);
        
        $invoices = $tenant->invoices()->active()->whereBetween('created_at', [$from, $to])
            ->where('branch_id', request('branch_id', $cBranch->id))
            ->with('details')
            ->get();

        if ($pmethod = request('payment_method')) {
            $paymentsByType = $paymentsByType->where('payment_method', $pmethod);
        }
        
        dump($invoices->pluck('details')->flatten()->toArray());


        //dump($paymentsByType->get()->toArray());

        /*$pdf = \PDF::loadView('tenant.income.index', [
            'branches' => $branches
        ]);

        return $pdf->download(uniqid('payments_', true) . '.pdf');*/

        return view('tenant.income.index', [
            'branches' => $branches,
            'payments_by_type' => $income = $paymentsByType->get(),
            'tot_charged' => $invoices->sum('total'),
            'tot_income' => $income->sum('amount_paid'),
            'tot_in_cash' => $income->where('payment_method', 1)->sum('amount_paid'),
            'tot_in_wire' => $income->where('payment_method', 2)->sum('amount_paid'),
            'tot_in_check' => $income->where('payment_method', 3)->sum('amount_paid'),
            'tot_fine' => $invoices->sum('fine_total'),
        ]);
    }
}
