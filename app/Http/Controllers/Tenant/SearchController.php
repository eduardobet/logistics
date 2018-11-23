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
            
            if ($qBranchCode && $qClientId) {
                $results = $tenant->clients()->with('branch')->where('manual_id', $qClientId);
                $client = true;
            } else {
                $results = $tenant->clients()->with('branch')->where('org_name', 'like', "%$term%")
                    ->orWhere('full_name', 'like', "%$term%");
                $client = true;

                if (!$results->count()) {
                    $data['cargo_entries'] = $tenant->cargoEntries()->with(['branch'])
                        ->where('trackings', 'like', "%$term%")->get();

                    $data['warehouses'] = $tenant->warehouses()
                        ->with(['toBranch', 'invoice'])
                        ->where('trackings', 'like', "%$term%")->get();

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
                                    $results = $tenant->clients()->with('branch')->where('manual_id', $qId);
                                    $client = true;
                                    break;
                                case 'i':
                                    $results = $tenant->invoices()->with(['client' => function ($query) {
                                        $query->with('branch')->select(['id', 'first_name', 'last_name', 'org_name']);
                                    }])->where('id', $qId);
                                    $inv = true;
                                    break;
                                case 'w':
                                    $results = $tenant->warehouses()->with(['client' => function ($query) {
                                        $query->with('branch')->select(['id', 'first_name', 'last_name', 'org_name']);
                                    },
                                    'fromBranch', 'toBranch',
                                    ])->where('id', $qId);
                                    $wh = true;
                                    break;
                                case 'r':
                                case 'reca':
                                    $results = $tenant->cargoEntries()->with(['branch'])->where('id', $qId);
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
