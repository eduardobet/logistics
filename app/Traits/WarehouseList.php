<?php

namespace Logistics\Traits;

trait WarehouseList
{
    /**
     * Gey warehouse list
     *
     * @return mixed
     */
    public function getWarehouses($tenant)
    {
        $branch = auth()->user()->currentBranch();

        if ($bId = request('branch_id')) {
            $branch = $tenant->branches->find($bId);
        }

        $searching = 'N';

        $warehouses = $tenant->warehouses()
            ->with(['fromBranch' => function ($query) {
                $query->select('id', 'code', 'name');
            }])
            ->withAndWhereHas('toBranch', function ($query) use ($branch) {
                $query->where('id', $branch->id)->select('id', 'code', 'name');
            });

        if (($from = request('from')) && ($to = request('to'))) {
            $warehouses = $warehouses->whereRaw(' date(warehouses.created_at) between ? and ? ', [$from, $to]);
            $searching = 'Y';
        }

        if ($type = request('type')) {
            $warehouses = $warehouses->whereRaw(' warehouses.type = ? ', [$type]);
            $searching = 'Y';
        }

        if ($searching == 'Y') {
            $warehouses = $warehouses->orderBy('warehouses.id')->get();
        } else {
            $warehouses = $warehouses->orderBy('warehouses.id')->paginate(20);
        }

        return [$warehouses, $searching, $branch];
    }
}
