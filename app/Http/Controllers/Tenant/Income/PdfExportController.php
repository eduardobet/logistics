<?php

namespace Logistics\Http\Controllers\Tenant\Income;

use Logistics\Traits\Tenant;
use Logistics\Traits\IncomeList;
use Logistics\Http\Controllers\Controller;

class PdfExportController extends Controller
{
    use Tenant, IncomeList;
    
    public function export()
    {
        [$branches, $incomes, $totCharged, $totIncome, $totInCash, $totInWire, $totInCheck, $totCommission, $totFine, $recas, $totClients] = $this->listIncomes($this->getTenant());

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

        if (request('pdf')) {

            $pdf = app('snappy.pdf.wrapper');
            $pdf->loadView('tenant.export.incomes-pdf', $data)->setOption("footer-right", "Pag. [page] / [topage]");

            return $pdf->download(uniqid('income_', true) . '.pdf');
        }

    }
}
