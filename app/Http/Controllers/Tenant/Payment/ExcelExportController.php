<?php

namespace Logistics\Http\Controllers\Tenant\Payment;

use Logistics\Exports\PaymentsExport;
use Logistics\Http\Controllers\Controller;

class ExcelExportController extends Controller
{
    public function export()
    {
        return (new PaymentsExport)->download(uniqid('payments_', true) . '.xlsx');
    }
}
