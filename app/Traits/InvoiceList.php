<?php

namespace Logistics\Traits;

trait InvoiceList
{
    /**
     * Gey invoice list
     *
     * @return mixed
     */
    public function getInvoices($tenant)
    {
        $user = auth()->user();
        $branch = $user->currentBranch();

        if ((($user->isSuperAdmin() || $user->isAdmin()) || $user->isWarehouse()) && $bId = request('branch_id')) {
            $branch = $tenant->branches->find($bId);
        }

        $searching = 'N';
        $statuses = ['A'];

        if (($user->isSuperAdmin() || $user->isAdmin()) && request('show_inactive') == '1') {
            $statuses = array_merge($statuses, ['I']);
        }

        $invoices = $tenant->invoices()
            ->whereIn('status', $statuses)
            ->withAndWhereHas('client', function ($query) {
                if ($clientId = request('client_id')) {
                    $query->where('id', $clientId)->select('id', 'manual_id', 'first_name', 'last_name');
                }
            })->with(['payments' => function ($query) {
                $query->active()->select('id', 'invoice_id', 'is_first', 'amount_paid');
            }])
            ->withAndWhereHas('branch', function ($query) use ($branch) {
                $query->where('id', $branch->id)->select('id', 'code', 'name', 'initial');
            });

        if ($user->isClient()) {
            $invoices  = $invoices->where('client_id', $user->client_id);
        }

        $now = \Carbon\Carbon::now();

        if (($from = request('from', $now->subDays(15)->format('Y-m-d'))) && ($to = request('to', date('Y-m-d')))) {
            $invoices = $invoices->whereRaw(' date(invoices.created_at) between ? and ? ', [$from, $to]);
            $searching = 'Y';
        }

        if (request('client_id')) {
            $searching = 'Y';
        }

        if (request('invoice_type')) {
            if (request('invoice_type') == '1') {
                $invoices = $invoices->whereNotNull('warehouse_id');
            } elseif (request('invoice_type') == '2') {
                $invoices = $invoices->whereNull('warehouse_id');
            }

            $searching = 'Y';
        }

        if ($searching == 'Y') {
            $invoices = $invoices->orderBy('invoices.manual_id')->get();
        } else {
            $invoices = $invoices->orderBy('invoices.manual_id')->paginate(20);
        }

        return [$invoices, $searching, $branch];
    }
}
