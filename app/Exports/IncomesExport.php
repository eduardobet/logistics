<?php

namespace Logistics\Exports;

use Logistics\Traits\Tenant;
use Logistics\Traits\IncomeList;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;

class IncomesExport implements FromView
{
    use Exportable, IncomeList, Tenant;

    public function view() : View
    {
        [$branches, $incomes, $totCharged, $totIncome, $totInCash, $totInWire, $totInCheck, $totCommission, $totFine, $recas, $totClients] = $this->listIncomes($this->getTenant());

        return view('tenant.export.incomes-excel', [
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
            'exporting' => true,
            'sign' => '',
            'show_total' => false,
        ]);
    }
}
