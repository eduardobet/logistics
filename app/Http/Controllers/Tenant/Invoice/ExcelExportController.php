<?php

namespace Logistics\Http\Controllers\Tenant\Invoice;

use Logistics\Exports\InvoicesExport;
use Logistics\Http\Controllers\Controller;

class ExcelExportController extends Controller
{
    public function export()
    {
        return (new InvoicesExport)->download(uniqid('invoices_', true) . '.xlsx');
    }
}
