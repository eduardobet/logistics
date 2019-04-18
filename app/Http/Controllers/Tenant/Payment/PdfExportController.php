<?php

namespace Logistics\Http\Controllers\Tenant\Payment;

use Logistics\Traits\Tenant;
use Logistics\Traits\PaymentList;
use Logistics\Http\Controllers\Controller;

class PdfExportController extends Controller
{
    use Tenant, PaymentList;
    
    public function export()
    {
        [$payments,, $branch] = $this->listPayments($this->getTenant());

        $data = [
            'payments' => $payments,
            'branch' => $branch,
            'exporting' => true,
            'sign' => '',
            'show_total' => true,
        ];

        if (request('pdf')) {

            $pdf = app('snappy.pdf.wrapper');
            $pdf->loadView( 'tenant.export.payments-pdf', $data)->setOption("footer-right", "Pag. [page] / [topage]");

            return $pdf->download(uniqid('payments_', true) . '.pdf');
        }
    }
}
