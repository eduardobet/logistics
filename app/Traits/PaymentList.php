<?php

namespace Logistics\Traits;

use Illuminate\Support\Facades\DB;

trait PaymentList
{
    /**
     * Gey payment list
     *
     * @return mixed
     */
    public function getPayments($tenant)
    {
        $branch = auth()->user()->currentBranch();

        if ($bId = request('branch_id')) {
            $branch = $tenant->branches->find($bId);
        }

        $searching = 'N';

        $payments = DB::table('payments')
            ->where('payments.tenant_id', '=', $tenant->id)
            ->join('invoices', 'payments.invoice_id', '=', 'invoices.id')
            ->join('branches', 'invoices.branch_id', '=', 'branches.id')
            ->join('clients', function ($join) {
                if ($clientId = request('client_id')) {
                    $join->on('invoices.client_id', '=', 'clients.id')
                        ->where('clients.id', '=', $clientId);
                } else {
                    $join->on('invoices.client_id', '=', 'clients.id');
                }
            })
            ->join('boxes', function ($join) use ($branch) {
                $join->on('clients.id', '=', 'boxes.client_id')
                    ->where('boxes.branch_id', '=', $branch->id)
                    ->where('boxes.status', '=', 'A');
            });

        if (($from = request('from')) && ($to = request('to'))) {
            $payments = $payments->whereRaw(' date(payments.created_at) between ? and ? ', [$from, $to]);
            $searching = 'Y';
        }

        if ($type = request('type')) {
            $payments = $payments->whereRaw(' payments.payment_method = ? ', [$type]);
            $searching = 'Y';
        }

        if (request('client_id')) {
            $searching = 'Y';
        }

        $payments = $payments->select(
            'payments.*',
            DB::raw("date_format(payments.created_at, '%d/%m/%Y') as created_at_dsp"),
            DB::raw("concat(clients.first_name, ' ', clients.last_name) as client_full_name"),
            'clients.id as client_id',
            'boxes.branch_code as client_box',
            'invoices.branch_id as invoice_branch_id',
            'branches.name as branch_name, branches.ruc, branches.dv, branches.telephones as branch_telephones, branches.address as branch_address'
        );

        if ($searching == 'Y') {
            $payments = $payments->orderBy('payments.id')->get();
        } else {
            $payments = $payments->orderBy('payments.id')->paginate(20);
        }

        return [$payments, $searching, $branch];
    }
}
