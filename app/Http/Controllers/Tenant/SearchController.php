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
        $cBranch = auth()->user()->currentBranch();
        $cBranchId = $cBranch->id;
        $superAdmin = auth()->user()->isSuperAdmin();
        $totResult = 0;

        if ($term = strtolower($term)) {
            $client = false;
            $wh = false;
            $inv = false;
            $reca = false;
            $tracking = false;

            $termPrefix = 'c|f|w|r|reca';
            preg_match("/($termPrefix)(\\d+)/i", $term, $matches);

            $client = false;

            $qType = @$matches[1];
            $qId = @$matches[2];

            if (in_array($qType, ['c', 'f', 'w', 'r', 'reca'])) {
                // searching client, invoice, warehouse, reca
                switch ($qType) {
                    case 'c':
                        $results = $tenant->clients();

                        if (!$superAdmin) {
                            $results = $results->withAndWhereHas('branch', function ($query) use ($cBranchId) {
                                $query->where('id', $cBranchId);
                            });
                        } else {
                            $results = $results->with('branch');
                        }

                        $results = $results->where('manual_id', $qId);

                        $client = true;
                        $totResult = $results->count();

                        break;
                    case 'f':
                        $results = $tenant->invoices();

                        if (!$superAdmin) {
                            $results = $results->where('branch_id', $cBranchId);
                        }

                        $results = $results->with(['client' => function ($query) {
                            $query->with('branch')->select(['id', 'manual_id', 'first_name', 'last_name', 'org_name', 'email']);
                        }])->where('id', $qId);
                        $inv = true;
                        $totResult = $results->count();

                        break;
                    case 'w':
                        $results = $tenant->warehouses();

                        if (!$superAdmin) {
                            $results = $results->withAndWhereHas('toBranch', function ($query) use ($cBranchId) {
                                $query->where('id', $cBranchId);
                            });
                        } else {
                            $results = $results->with('toBranch');
                        }

                        $results = $results->with([
                            'client' => function ($query) {
                                $query->with('branch')->select(['id', 'manual_id', 'first_name', 'last_name', 'org_name', 'email']);
                            },
                            'fromBranch',
                        ])->where('id', $qId);
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
                $branchesPrefix = implode($bcodes = $this->getBranches()->pluck('code')->toArray(), '|');
                preg_match("/($branchesPrefix)(\\d+)/i", $term, $matches);
                $qBranchCode = @$matches[1];
                $qClientId = @$matches[2];

                if (in_array(strtoupper($qBranchCode), $bcodes)) {
                    // searching client from branch by id
                    if ($qBranchCode === strtolower($cBranch->code)) {
                        $results = $tenant->clients()
                            ->where('branch_id', $cBranchId)
                            ->with('branch')->where('manual_id', $qClientId);
                        $client = true;
                        $totResult = $results->count();
                    } else {
                        $results = $tenant->clients();

                        if (!$superAdmin) {
                            $results = $results->withAndWhereHas('branch', function ($query) use ($cBranchId) {
                                $query->where('id', $cBranchId);
                            });
                        } else {
                            $results = $results->with('branch')->where('id', $cBranchId);
                        }
                        
                        $client = true;
                        $totResult = $results->count();
                    } // not current branch
                } // searching client
                
                if (!$totResult) {
                    // trying to search client by name
                    $results = $tenant->clients();

                    if (!$superAdmin) {
                        $results = $results->withAndWhereHas('branch', function ($query) use ($cBranchId) {
                            $query->where('id', $cBranchId);
                        });
                    } else {
                        $results = $results->with('branch');
                    }

                    $results = $results->where(function ($query) use ($term) {
                        $query->where('full_name', 'like', "%$term%")->orWhere('org_name', 'like', "%$term%")->orWhere('email', 'like', "%$term%");
                    });

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
                            $query->where('id', $cBranchId);
                        });
                    } else {
                        $warehouses = $warehouses->with('toBranch');
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

    public function searchOld()
    {
        $tenant = $this->getTenant();
        $term = request('q', '');
        $cBranch = auth()->user()->currentBranch();
        $cBranchId = $cBranch->id;
        $superAdmin = auth()->user()->isSuperAdmin();

        if ($term = strtolower($term)) {
            $branchesPrefix = implode($this->getBranches()->pluck('code')->toArray(), '|');
            preg_match("/($branchesPrefix)(\\d+)/i", $term, $matches);
            $qBranchCode = @$matches[1];
            $qClientId = @$matches[2];
            $client = false;
            $wh = false;
            $inv = false;
            $reca = false;
            $tracking = false;

            if ($qBranchCode && $qClientId && $qBranchCode === strtolower($cBranch->code)) {
                $results = $tenant->clients()
                    ->where('branch_id', $cBranchId)
                    ->with('branch')->where('manual_id', $qClientId);
                $client = true;
            } else {
                $results = $tenant->clients();

                if (!$superAdmin) {
                    $results = $results->withAndWhereHas('branch', function ($query) use ($cBranchId) {
                        $query->where('id', $cBranchId);
                    });
                } else {
                    $results = $results->with('branch');
                }

                $results = $results->where(function ($query) use ($term) {
                    $query->where('full_name', 'like', "%$term%")->orWhere('org_name', 'like', "%$term%")->orWhere('email', 'like', "%$term%");
                });

                $client = true;

                if (!$results->count()) {
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
                            $query->where('id', $cBranchId);
                        });
                    } else {
                        $warehouses = $warehouses->with('toBranch');
                    }

                    $data['warehouses'] = $warehouses->with('invoice')->where('trackings', 'like', "%$term%")->get();

                    if ($data['cargo_entries']->count() || $data['warehouses']->count()) {
                        $tracking = true;
                        $client = false;
                        $results = collect($data);
                    }

                    if (!$tracking) {
                        $termPrefix = 'c|i|w|r|reca';
                        preg_match("/($termPrefix)(\\d+)/i", $term, $matches);

                        $client = false;
    
                        $qType = @$matches[1];
                        $qId = @$matches[2];

                        if ($qType && $qId) {
                            switch ($qType) {
                                case 'c':
                                    $results = $tenant->clients();

                                    if (!$superAdmin) {
                                        $results = $results->withAndWhereHas('branch', function ($query) use ($cBranchId) {
                                            $query->where('id', $cBranchId);
                                        });
                                    } else {
                                        $results = $results->with('branch');
                                    }

                                    $results = $results->where('manual_id', $qId);

                                    $client = true;
                                    break;
                                case 'i':
                                    $results = $tenant->invoices();

                                    if (!$superAdmin) {
                                        $results = $results->where('branch_id', $cBranchId);
                                    }

                                    $results = $results->with(['client' => function ($query) {
                                        $query->with('branch')->select(['id', 'manual_id', 'first_name', 'last_name', 'org_name', 'email']);
                                    }])->where('id', $qId);
                                    $inv = true;
                                    break;
                                case 'w':
                                    $results = $tenant->warehouses();

                                    if (!$superAdmin) {
                                        $results = $results->withAndWhereHas('toBranch', function ($query) use ($cBranchId) {
                                            $query->where('id', $cBranchId);
                                        });
                                    } else {
                                        $results = $results->with('toBranch');
                                    }

                                    $results = $results->with(['client' => function ($query) {
                                        $query->with('branch')->select(['id', 'manual_id', 'first_name', 'last_name', 'org_name', 'email']);
                                    },
                                    'fromBranch',
                                    ])->where('id', $qId);
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
                                    break;
                                default:
                                    # code...
                                    break;
                            }
                        }
                    }
                }
            }

            if (!$tracking) {
                $results = $results->paginate(20);
            }

            if ($results->count()) {
                return view('tenant.search.results', [
                    'results' => $results,
                    'client' => $client,
                    'wh' => $wh,
                    'inv' => $inv,
                    'reca' => $reca,
                    'tracking' => $tracking,
                ]);
            }

            return view('tenant.search.results', [
                'noresults' => __('Your search - ":term" - did not match any documents.', ['term' => $term]),
            ]);
        }
        
        if (!$term || !$results->count()) {
            return view('tenant.search.results', [
                'noresults' => __('Your search - ":term" - did not match any documents.', ['term' => $term]),
            ]);
        }
    }
}
