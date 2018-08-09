<?php

namespace Logistics\Exports;

use Logistics\Traits\Tenant;
use Logistics\Traits\WarehouseList;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;

class WarehousesExport implements FromView
{
    use Exportable, WarehouseList, Tenant;

    public function view() : View
    {
        [$warehouses, $searching] = $this->getWarehouses($this->getTenant());

        return view('tenant.export.warehouses-excel', [
            'warehouses' => $warehouses,
            'exporting' => true,
            'sign' => '',
        ]);
    }
}
