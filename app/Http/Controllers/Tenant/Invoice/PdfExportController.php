<?php

namespace Logistics\Http\Controllers\Tenant\Invoice;

use Logistics\Traits\Tenant;
use Logistics\Traits\InvoiceList;
use Logistics\Http\Controllers\Controller;

class PdfExportController extends Controller
{
    use Tenant, InvoiceList;
    
    public function export()
    {
        [$invoices,, $branch] = $this->listInvoices($this->getTenant());

        $data = [
            'invoices' => $invoices,
            'branch' => $branch,
            'exporting' => true,
            'sign' => '',
            'show_total' => true,
        ];

        if (request('pdf')) {

            $pdf = app('dompdf.wrapper');
            $pdf->getDomPDF()->set_option("enable_php", true);
            $pdf->loadView( 'tenant.export.invoices-pdf', $data);

            return $pdf->download(uniqid('invoices_', true) . '.pdf');
        }
    }
}
