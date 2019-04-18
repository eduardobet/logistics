<?php

namespace Logistics\Http\Controllers\Tenant;

use Logistics\Traits\Tenant;
use Logistics\Traits\IncomeList;
use Logistics\Exports\IncomesExport;
use Logistics\Http\Controllers\Controller;

class IncomeController extends Controller
{
    use Tenant, IncomeList;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (request('_excel_it')) {
            return (new IncomesExport)->download(uniqid('incomes_', true) . '.xlsx');
        }
        
        [$branches, $incomes, $totCharged, $totIncome, $totInCash, $totInWire, $totInCheck, $totCommission, $totFine, $recas, $totClients] = $this->listIncomes( $this->getTenant());
        
        $data = [
            'branches' => $branches,
            'payments_by_type' => $incomes,
            'tot_charged' => $totCharged,
            'tot_income' => $totIncome,
            'tot_in_cash' => $totInCash,
            'tot_in_wire' => $totInWire,
            'tot_in_check' => $totInCheck,
            'tot_commission' => $totCommission,
            'tot_fine' => $totFine,
            'recas' => $recas,
            'tot_clients' => $totClients,
            'printing' => 1,
            'sign' => '$',
            'show_total' => true,
        ];

        if (request('_print_it')) {

            $pdf = app('dompdf.wrapper');
            $pdf->getDomPDF()->set_option("enable_php", true);
            $pdf->loadView( 'tenant.export.incomes-pdf', $data);

            return $pdf->download(uniqid('income_', true) . '.pdf');
        }

        return view('tenant.income.index', $data);
    }
}
