<?php

namespace Logistics\Http\Controllers\Tenant\Warehouse;

use Logistics\Traits\Tenant;
use Logistics\Traits\WarehouseList;
use Logistics\Http\Controllers\Controller;

class PdfExportController extends Controller
{
    use Tenant, WarehouseList;
    
    public function export()
    {
        [$warehouses,, $branch] = $this->listWarehouses($this->getTenant());

        $data = [
            'warehouses' => $warehouses,
            'branch' => $branch,
            'exporting' => true,
            'sign' => '',
        ];

        if (request('pdf')) {

            $pdf = app('snappy.pdf.wrapper');
            $pdf->loadView('tenant.export.warehouses-pdf', $data)->setOption("footer-right", "Pag. [page] / [topage]");

            return $pdf->download(uniqid('warehouses_', true) . '.pdf');
        }
    }
}
