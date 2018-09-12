<?php

namespace Logistics\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
use Illuminate\Support\Facades\Validator;
use Logistics\Http\Controllers\Controller;

class CargoEntryController extends Controller
{
    use Tenant;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $branch = auth()->user()->currentBranch();

        $cargoEntries = $this->getTenant()->cargoEntries()->with([
            'branch' => function ($branch) {
                $branch->select(['id', 'name']);
            },
            'creator' => function ($creator) {
                $creator->select(['id', 'first_name', 'last_name',]);
            }
        ])
        ->where('branch_id', $branch->id);

        $searching = 'N';

        if (($from = request('from')) && ($to = request('to'))) {
            $cargoEntries = $cargoEntries->whereRaw(' date(cargo_entries.created_at) between ? and ? ', [$from, $to]);
            $searching = 'Y';
        }

        if ($branch = request('branch_id')) {
            $cargoEntries = $cargoEntries->where('branch_id', $branch);
            $searching = 'Y';
        }

        $cargoEntries = $cargoEntries->orderBy('id', 'DESC')->paginate(15);

        return view('tenant.warehouse.cargo-entry.list', [
            'cargo_entries' => $cargoEntries,
            'searching' => $searching,
            'branches' => $this->branches(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('tenant.warehouse.cargo-entry.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'branch_id' => 'required|integer',
            'trackings' => 'required',
        ]);

        $tenant = $this->getTenant();

        if ($validation->fails()) {
            return redirect()->route('tenant.warehouse.cargo-entry.create', $tenant->domain)
                ->withErrors($validation)
                ->withInput();
        }

        $cargoEntry = $tenant->cargoEntries()->create([
            'branch_id' => $request->branch_id,
            'trackings' => $request->trackings,
        ]);

        if ($cargoEntry) {
            return redirect()->route('tenant.warehouse.cargo-entry.create', $tenant->domain)
                ->with('flash_success', __('The :what has been created.', ['what' => __('Cargo entry')]));
        }

        return redirect()->route('tenant.warehouse.cargo-entry.create', $tenant->domain)
            ->withInput()
            ->with('flash_error', __('Error while trying to :action :what', [
                'action' => __('Save'),
                'what' => __('The cargo entry'),
            ]));
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $domain
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($domain, $id)
    {
        $tenant = $this->getTenant();
        $cargoEntry = $tenant->cargoEntries()->with([
            'branch' => function ($branch) {
                $branch->select(['id', 'name']);
            },
            'creator' => function ($creator) {
                $creator->select(['id', 'first_name', 'last_name', ]);
            }
        ])->findOrFail($id);

        return view('tenant.warehouse.cargo-entry.show', ['cargo_entry' => $cargoEntry]);
    }
}
