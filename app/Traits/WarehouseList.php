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
        $user = auth()->user();
        $branch = $user->currentBranch();

        if (($user->isSuperAdmin() || !$user->isWarehouse()) && $bId = request('branch_id')) {
            $branch = $tenant->branches->find($bId);
        }

        $searching = 'N';
        $statuses = ['A'];

        if ($user->isSuperAdmin() && request('show_inactive') == '1') {
            $statuses = array_merge($statuses, ['I']);
        }

        $warehouses = $tenant->warehouses()
            ->whereIn('status', $statuses)
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
            $warehouses = $warehouses->orderBy('warehouses.manual_id')->get();
        } else {
            $warehouses = $warehouses->orderBy('warehouses.manual_id')->paginate(20);
        }

        return [$warehouses, $searching, $branch];
    }
}
