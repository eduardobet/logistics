<?php

namespace Logistics\Exports;

use Logistics\Traits\Tenant;
use Logistics\Traits\PaymentList;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;

class PaymentsExport implements FromView
{
    use Exportable, PaymentList, Tenant;

    public function view() : View
    {
        [$payments, $searching] = $this->getPayments($this->getTenant());

        return view('tenant.export.payments-excel', [
            'payments' => $payments,
            'exporting' => true,
            'sign' => '',
        ]);
    }
}
