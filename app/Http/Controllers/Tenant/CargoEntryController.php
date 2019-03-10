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
        $user = auth()->user();
        $branch = $user->currentBranch();

        $cargoEntries = $this->getTenant()->cargoEntries()
            ->with([
                'branch' => function ($branch) {
                    $branch->select(['id', 'name']);
                },
                'creator' => function ($creator) {
                    $creator->select(['id', 'first_name', 'last_name',]);
                }
             ]);

        $searching = 'N';

        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->isWarehouse()) {
            $cargoEntries = $cargoEntries->where('branch_id', $branch->id);
        } else {
            if ($branchId = request('branch_id')) {
                $searching = 'Y';
                $cargoEntries = $cargoEntries->where('branch_id', $branchId);
            }
        }

        if (($from = request('from')) && ($to = request('to'))) {
            $cargoEntries = $cargoEntries->whereRaw(' date(cargo_entries.created_at) between ? and ? ', [$from, $to]);
            $searching = 'Y';
        }

        if ($type = request('type')) {
            if ($type == 'M') {
                $cargoEntries = $cargoEntries->where('type', $type)->orWhereNull('type');
            } elseif ($type == 'M') {
                $cargoEntries = $cargoEntries->where('type', $type);
            }
            $searching = 'Y';
        }

        $cargoEntries = $cargoEntries->orderBy('id', 'DESC')->paginate(15);

        $branches = $this->getBranches();

        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->isWarehouse()) {
            $branches = $branches->where('id', $branch->id);
        }

        return view('tenant.warehouse.cargo-entry.list', [
            'cargo_entries' => $cargoEntries,
            'searching' => $searching,
            'branches' => $branches,
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
            'type' => 'sometimes|in:N,M',
            'weight' => 'nullable|integer',
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
            'type' => $request->type,
            'weight' => $request->weight ?: $request->weight,
        ]);

        if ($cargoEntry) {
            return redirect()->route('tenant.warehouse.cargo-entry.show', [$tenant->domain, $cargoEntry->id])
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
        $user = auth()->user();
        $cargoEntry = $tenant->cargoEntries()->with([
            'branch' => function ($branch) {
                $branch->select(['id', 'name']);
            },
            'creator' => function ($creator) {
                $creator->select(['id', 'first_name', 'last_name', ]);
            }
        ]);

        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->isWarehouse()) {
            $cargoEntry = $cargoEntry->where('branch_id', $user->currentBranch()->id);
        }

        $cargoEntry = $cargoEntry->findOrFail($id);

        return view('tenant.warehouse.cargo-entry.show', ['cargo_entry' => $cargoEntry]);
    }
}
