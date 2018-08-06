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
            
            if ($qBranchCode && $qClientId) {
                $results = $tenant->clients()->where('id', $qClientId);
            } else {
                $results = $tenant->clients()->where('org_name', 'like', "%$term%")
                    ->orWhere('full_name', 'like', "%$term%");

                if (!$results->count()) {
                    $termPrefix = 'c|i|w';
                    preg_match("/($termPrefix)(\\d+)/i", $term, $matches);

                    $qType = @$matches[1];
                    $qId = @$matches[2];

                    if ($qType && $qId) {
                        switch ($qType) {
                            case 'c':
                                $results = $tenant->clients()->with('boxes')->where('id', $qId);
                                break;
                            case 'i':
                                $results = $tenant->invoices()->with('client')->where('id', $qId);
                                break;
                            
                            default:
                                # code...
                                break;
                        }
                    }
                }
            }

            dd($results->get()->toArray());
        } else {
            dd("WTF: i am not searching empty term");
        }
    }

    private function multineedle_stripos($haystack, $needles, $offset = 0)
    {
        foreach ($needles as $needle) {
            $found[$needle] = stripos($haystack, $needle, $offset);
        }
        return $found;
    }
}
