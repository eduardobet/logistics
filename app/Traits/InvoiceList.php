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
        $branch = auth()->user()->currentBranch();

        if ($bId = request('branch_id')) {
            $branch = $tenant->branches->find($bId);
        }

        $searching = 'N';

        $invoices = $tenant->invoices()
            ->withAndWhereHas('client', function ($query) {
                if ($clientId = request('client_id')) {
                    $query->where('id', $clientId)->select('id', 'first_name', 'last_name');
                }
            })->with(['payments' => function ($query) {
                $query->select('id', 'invoice_id', 'is_first', 'amount_paid');
            }])
            ->withAndWhereHas('branch', function ($query) use ($branch) {
                $query->where('id', $branch->id)->select('id', 'code', 'name', 'initial');
            });

        if (($from = request('from')) && ($to = request('to'))) {
            $invoices = $invoices->whereRaw(' date(invoices.created_at) between ? and ? ', [$from, $to]);
            $searching = 'Y';
        }

        if (request('client_id')) {
            $searching = 'Y';
        }

        if (request('invoice_type')) {
            if (request('invoice_type') == '1') {
                $invoices = $invoices->whereNotNull('warehouse_id');
            }else if (request('invoice_type') == '2') {
                $invoices = $invoices->whereNull('warehouse_id');
            }

            $searching = 'Y';
        }

        if ($searching == 'Y') {
            $invoices = $invoices->orderBy('invoices.id')->get();
        } else {
            $invoices = $invoices->orderBy('invoices.id')->paginate(20);
        }

        return [$invoices, $searching, $branch];
    }
}
