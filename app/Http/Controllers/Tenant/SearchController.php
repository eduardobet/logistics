<?php

namespace Logistics\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
use Logistics\Http\Controllers\Controller;

class SearchController extends Controller
{
    use Tenant;

    public function search()
    {
        $tenant = $this->getTenant();
        $term = request('q', '');
        $user = auth()->user();
        $cBranch = $user->currentBranch();
        $cBranchId = $cBranch->id;
        $superAdmin = $user->isSuperAdmin();
        $iswh = $user->isWarehouse();
        $totResult = 0;
        $statuses = ['A'];

        if ($term = strtolower($term)) {
            $client = false;
            $wh = false;
            $inv = false;
            $reca = false;
            $tracking = false;

            $client = false;

            $qType = preg_replace('/[^a-zA-Z]/', '', $term);
            $qId =  preg_replace('/[^0-9]/', '', $term);

            if (in_array($qType, ['c', 'f', 'i', 'w', 'wa', 'a', 'r', 'reca'])) {
                // searching client, invoice, warehouse, reca
                switch ($qType) {
                    case 'c':
                        $results = $tenant->clients();

                        if (!$superAdmin && !$iswh) {
                            $results = $results->withAndWhereHas('branch', function ($query) use ($cBranchId) {
                                $query->where('id', $cBranchId);
                            });
                        } else {
                            $results = $results->with('branch');
                        }

                        $results = $results->with(['clientInvoices' => function ($invoice) {
                            $invoice->where('is_paid', false)->where('status', 'A')
                            ->with('payments');
                        }])->where('manual_id', $qId);
         
                        $client = true;
                        $totResult = $results->count();

                        break;
                    case 'f':
                    case 'i':
                        $results = $tenant->invoices();

                        if (!$superAdmin && !$iswh) {
                            $results = $results->where('branch_id', $cBranchId);
                        }

                        $results = $results->with(['client' => function ($query) {
                            $query->with('branch')->select(['id', 'manual_id', 'first_name', 'last_name', 'org_name', 'email']);
                        }])->where('manual_id', $qId);
                        $inv = true;
                        $totResult = $results->count();

                        break;
                    case 'w':
                    case 'wa':
                    case 'a':
                        $results = $tenant->warehouses();

                        if (!$superAdmin && !$iswh) {
                            $results = $results->withAndWhereHas('toBranch', function ($query) use ($cBranchId) {
                                $query->where('id', $cBranchId)->where('status', 'A');
                            });
                        } else {
                            $statuses = array_merge($statuses, ['I']);
                            $results = $results->whereIn('status', $statuses)->with('toBranch');
                        }

                        $results = $results->with([
                            'client' => function ($query) {
                                $query->with('branch')->select(['id', 'manual_id', 'first_name', 'last_name', 'org_name', 'email']);
                            },
                            'fromBranch',
                        ])->where('manual_id', $qId);
                        $totResult = $results->count();

                        $wh = true;
                        break;
                    case 'r':
                    case 'reca':
                        $results = $tenant->cargoEntries();

                        if (!$superAdmin) {
                            $results = $results->withAndWhereHas('branch', function ($query) use ($cBranchId) {
                                $query->where('id', $cBranchId);
                            });
                        } else {
                            $results = $results->with('branch');
                        }

                        $results = $results->where('id', $qId);

                        $reca = true;
                        $totResult = $results->count();

                        break;
                    default:
                                    # code...
                        break;
                 }
            } else {
                $bcodes = $this->getBranches()->pluck('code')->toArray();

                $qBranchCode = preg_replace('/[^a-zA-Z]/', '', $term);
                $qClientId =  preg_replace('/[^0-9]/', '', $term);

                if (in_array(strtoupper($qBranchCode), $bcodes)) {
                    // searching client from branch by id
                    $results = $tenant->clients()
                            ->where('branch_id', $cBranchId)
                            ->with('branch')->where('manual_id', $qClientId);
                    $client = true;
                        
                    $results = $results->with(['clientInvoices' => function ($invoice) {
                        $invoice->where('is_paid', false)->where('status', 'A')
                                ->with('payments');
                    }]);

                    $totResult = $results->count();
                } // searching client
                
                if (!$totResult) {
                    // trying to search client by name
                    $results = $tenant->clients();

                    if (!$superAdmin && !$iswh) {
                        $results = $results->withAndWhereHas('branch', function ($query) use ($cBranchId) {
                            $query->where('id', $cBranchId);
                        });
                    } else {
                        $results = $results->with('branch');
                    }

                    $results = $results->where(function ($query) use ($term) {
                        $query->where('full_name', 'like', "%$term%")->orWhere('org_name', 'like', "%$term%")->orWhere('email', 'like', "%$term%");
                    });

                    $results = $results->with(['clientInvoices' => function ($invoice) {
                        $invoice->where('is_paid', false)->where('status', 'A')
                            ->with('payments');
                    }]);

                    $client = true;
                    $totResult = $results->count();
                } // nothing found so far

                if (!$totResult) {
                    // trying to search in cargo entries or warehouses by tracking number
                    $cargoEntries = $tenant->cargoEntries();

                    if (!$superAdmin) {
                        $cargoEntries = $cargoEntries->withAndWhereHas('branch', function ($query) use ($cBranchId) {
                            $query->where('id', $cBranchId);
                        });
                    } else {
                        $cargoEntries = $cargoEntries->with('branch');
                    }

                    $data['cargo_entries'] = $cargoEntries->where('trackings', 'like', "%$term%")->get();

                    $warehouses = $tenant->warehouses();

                    if (!$superAdmin) {
                        $warehouses = $warehouses->withAndWhereHas('toBranch', function ($query) use ($cBranchId) {
                            $query->where('id', $cBranchId)->where('status', 'A');
                        });
                    } else {
                        $statuses = array_merge($statuses, ['I']);
                        $warehouses = $warehouses->whereIn('status', $statuses)->with('toBranch');
                    }

                    $data['warehouses'] = $warehouses->with('invoice')->where('trackings', 'like', "%$term%")->get();
                    $totC = $data['cargo_entries']->count();
                    $totW = $data['warehouses']->count();


                    if ($totC || $totW) {
                        $tracking = true;
                        $client = false;
                        $results = collect($data);
                    }

                    $totResult = max($totC, $totW);
                } // nothing found so far
            } // else

            if (!$totResult) {
                return view('tenant.search.results', [
                    'noresults' => __('Your search - ":term" - did not match any documents.', ['term' => $term]),
                ]);
            }

            if (!$tracking) {
                $results = $results->paginate(10);
            }

            return view('tenant.search.results', [
                'results' => $results,
                'client' => $client,
                'wh' => $wh,
                'inv' => $inv,
                'reca' => $reca,
                'tracking' => $tracking,
            ]);
        } // if term
        else {
            return view('tenant.search.results', [
                'noresults' => __('Your search - ":term" - did not match any documents.', ['term' => $term]),
            ]);
        }
    }
}
