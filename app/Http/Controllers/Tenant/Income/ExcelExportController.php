<?php

namespace Logistics\Http\Controllers\Tenant\Income;

use Logistics\Exports\IncomesExport;
use Logistics\Http\Controllers\Controller;

class ExcelExportController extends Controller
{
    public function export()
    {
        return (new IncomesExport)->download(uniqid('income_', true) . '.xlsx');
    }
}
