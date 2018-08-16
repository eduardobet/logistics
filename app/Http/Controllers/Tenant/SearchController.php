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

        if ($term) {
            $branchesPrefix = implode($this->branches()->pluck('code')->toArray(), '|');
            preg_match("/($branchesPrefix)(\\d+)/i", $term, $matches);
            $qBranchCode = @$matches[1];
            $qClientId = @$matches[2];
            $client = false;
            $wh = false;
            $inv = false;
            
            if ($qBranchCode && $qClientId) {
                $results = $tenant->clients()->with('boxes')->where('id', $qClientId);
                $client = true;
            } else {
                $results = $tenant->clients()->with('boxes')->where('org_name', 'like', "%$term%")
                    ->orWhere('full_name', 'like', "%$term%");
                $client = true;

                if (!$results->count()) {
                    $termPrefix = 'c|i|w';
                    preg_match("/($termPrefix)(\\d+)/i", $term, $matches);

                    $client = false;
    
                    $qType = @$matches[1];
                    $qId = @$matches[2];

                    if ($qType && $qId) {
                        switch ($qType) {
                            case 'c':
                                $results = $tenant->clients()->with('boxes')->where('id', $qId);
                                $client = true;
                                break;
                            case 'i':
                                $results = $tenant->invoices()->with(['client' => function ($query) {
                                    $query->with('boxes')->select(['id', 'first_name', 'last_name', 'org_name']);
                                }])->where('id', $qId);
                                $inv = true;
                                break;
                            case 'w':
                                $results = $tenant->warehouses()->with(['client' => function ($query) {
                                    $query->with('boxes')->select(['id', 'first_name', 'last_name', 'org_name']);
                                },
                                'fromBranch', 'toBranch',
                                ])->where('id', $qId);
                                $wh = true;
                                break;
                            
                            default:
                                # code...
                                break;
                        }
                    }
                }
            }

            $results = $results->paginate(20);

            if ($results->count()) {
                return view('tenant.search.results', [
                    'results' => $results,
                    'client' => $client,
                    'wh' => $wh,
                    'inv' => $inv,
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
