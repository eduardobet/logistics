<?php

namespace Logistics\Exports;

use Logistics\Traits\Tenant;
use Logistics\Traits\InvoiceList;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;

class InvoicesExport implements FromView
{
    use Exportable, InvoiceList, Tenant;

    public function view() : View
    {
        [$invoices, $searching] = $this->getInvoices($this->getTenant());

        return view('tenant.export.invoices-excel', [
            'invoices' => $invoices,
            'exporting' => true,
            'sign' => '',
        ]);
    }
}
