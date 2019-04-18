<?php

namespace Logistics\Http\Controllers\Tenant\Warehouse;

use Logistics\Exports\WarehousesExport;
use Logistics\Http\Controllers\Controller;

class ExcelExportController extends Controller
{
    public function export()
    {
        return (new WarehousesExport)->download(uniqid( 'warehouses_', true) . '.xlsx');
    }
}
