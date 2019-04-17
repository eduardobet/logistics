<?php

namespace Logistics\Http\Controllers\Tenant\Invoice;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
use Illuminate\Support\Fluent;
use Logistics\DB\Tenant\Client;
use Logistics\DB\Tenant\Payment;
use Logistics\Traits\InvoiceList;
use Illuminate\Support\Facades\Validator;
use Logistics\Http\Controllers\Controller;
use Logistics\Http\Requests\Tenant\InvoiceRequest;
use Logistics\Jobs\Tenant\SendInvoiceCreatedEmail;
use Logistics\Notifications\Tenant\InvoiceActivity;

class OutstandingsController extends Controller
{
    use Tenant; //, InvoiceList;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list()
    {
        $tenant = $this->getTenant();
        $user = auth()->user();
        $clients = $tenant->clients();

        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->isWarehouse()) {
            $clients = $clients->where('branch_id', $user->currentBranch()->id);
        }

        $searching = 'N';

        if (($user->isSuperAdmin() || $user->isAdmin() || $user->isWarehouse()) && $branch = request('branch_id')) {
            $clients = $clients->where('branch_id', $branch);
            $searching = 'Y';
        }

        $clients = $clients->withAndWhereHas('clientInvoices', function ($invoices) {
            $invoices->where('status', 'A')->where('is_paid', false)->where('due_at', '<=', \Carbon\Carbon::now()->format('Y-m-d'))
                ->select(['client_id', \DB::raw( "invoices.total - COALESCE(( select SUM(p.amount_paid) from payments p where p.status = 'A' and p.invoice_id = invoices.id ),0) as pending ")]);
        })
            ->with(['branch' => function($branch){
                $branch->select('id', 'code');
            }]);

        $clients = $clients->orderBy('manual_id')->paginate(100);

        $branches = $this->getBranches();

        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->isWarehouse()) {
            $branches = $branches->where('id', $user->currentBranch()->id);
        }

        return view('tenant.invoice.outstandings.index', [
            'clients' => $clients,
            'branches' => $branches,
            'searching' => $searching,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function details()
    {
        $tenant = $this->getTenant();

        $client = $tenant->clients()->where('id', request('client_id'))
            ->where('branch_id', request('branch_id'))
            ->withAndWhereHas('clientInvoices', function ($invoices) {
                $invoices->where('status', 'A')->where('is_paid', false)->where('due_at', '<=', \Carbon\Carbon::now()->format('Y-m-d'))
                    ->select(['id','client_id', 'total', 'warehouse_id', 'created_at', 'manual_id', 'branch_id', \DB::raw("invoices.total - COALESCE(( select SUM(p.amount_paid) from payments p where p.status = 'A' and p.invoice_id = invoices.id ),0) as pending ")])
                    ->orderBy('manual_id');
            })
            ->firstOrFail();

        return view('tenant.invoice.outstandings.details', [
            'client' => $client,
        ]);
     }
}
