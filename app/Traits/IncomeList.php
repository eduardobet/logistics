<?php

namespace Logistics\Traits;

use Carbon\Carbon;

trait IncomeList
{
    /**
     * Gey invoice list
     *
     * @return mixed
     */
    public function listIncomes($tenant)
    {
        $branches = $this->getBranches();
        $cBranch = auth()->user()->currentBranch();

        $from =  Carbon::parse(request('from', date('Y-m-d')))->startOfDay();
        $to =  Carbon::parse(request('to', date('Y-m-d')))->endOfDay();

        $paymentsByType = $tenant->payments()->active()
            ->with(['invoice' => function ($invoice) {
                $invoice->with('warehouse');
            }])
            ->whereHas('invoice', function ($invoice) use ($cBranch) {
                $invoice->active()->where('branch_id', request('branch_id', $cBranch->id));

                if($invoiceId = request('manual_id')) {
                    $invoice->where('manual_id', $invoiceId);
                }
            });


        $paymentsByType = $paymentsByType
            ->orderBy('created_at')
            ->orderBy('invoice_id')
            ->whereBetween('created_at', [$from, $to]);

        $invoices = $tenant->invoices()->active()->whereBetween('created_at', [$from, $to])
            ->where('branch_id', request('branch_id', $cBranch->id))
            ->with(['details' => function ($detail) {
                $detail->whereHas('productType', function ($pType) {
                    $pType->where('is_commission', '=', true);
                });
            }]);

        if($invoiceId = request('manual_id')) {
            $invoices = $invoices->where('manual_id', $invoiceId);
        }

        $invoices = $invoices->get();

        if ($pmethod = request('type')) {
            $paymentsByType = $paymentsByType->where('payment_method', $pmethod);
        }

        $details = $invoices->where('warehouse_id', '=', null)
            ->pluck('details')->flatten();

        $commissions = $details;

        $recas = $tenant->cargoEntries()->whereBetween('created_at', [$from, $to])
            ->where('branch_id', request('branch_id', $cBranch->id))
            ->where('weight', '>', 0)
            ->get();

        $incomes = $paymentsByType->get();

        $totCharged = $invoices->sum('total');
        $totIncome = $incomes->sum('amount_paid');
        $totInCash = $incomes->where('payment_method', 1)->sum('amount_paid');
        $totInWire = $incomes->where('payment_method', 2)->sum('amount_paid');
        $totInCheck = $incomes->where('payment_method', 3)->sum('amount_paid');
        $totCommission = $commissions->sum('total');
        $totFine = $invoices->sum('fine_total');

        return [
            $branches, $incomes, $totCharged, $totIncome, $totInCash, $totInWire, $totInCheck, $totCommission, $totFine, $recas
        ];
    }
}
