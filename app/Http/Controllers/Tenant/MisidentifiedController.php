<?php

namespace Logistics\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
use Illuminate\Support\Facades\Validator;
use Logistics\Http\Controllers\Controller;

class MisidentifiedController extends Controller
{
    use Tenant;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tenant = $this->getTenant();

        $branch = auth()->user()->currentBranch();
        $user = auth()->user();

        $searching = 'N';
        $misidentified = $tenant->misidentifiedPackages()
            ->with(['toBranch', 'client', 'cargoEntry'])
            ->orderBy('id', 'DESC');

        if (($from = request('from')) && ($to = request('to'))) {
            $misidentified = $misidentified->whereRaw(' date(misidentified_packages.created_at) between ? and ? ', [$from, $to]);
            $searching = 'Y';
        }

        if (!$user->isSuperAdmin() && !$user->isWarehouse()) {
            $misidentified = $misidentified->where('branch_to', $branch->id);
        } else {
            if ($branchId = request('branch_id')) {
                $searching = 'Y';
                $misidentified = $misidentified->where('branch_to', $branchId);
            }
        }

        $misidentified = $misidentified->paginate(20);

        $branches = $this->getBranches();

        if (!$user->isSuperAdmin() && !$user->isWarehouse()) {
            $branches = $branches->where('id', $branch->id);
        }

        return view('tenant.misidentified-package.index', [
            'misidentified_packages' => $misidentified,
            'searching' => $searching,
            'branches' => $branches->pluck('name', 'id'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tenant = $this->getTenant();

        return view('tenant.misidentified-package.create', [
            'branches' => $this->getBranches()->pluck('name', 'id'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->validates($request);

        if ($validator->fails()) {
            return response()->json([
                'msg' => $validator->errors()->first(),
                'error' => true,
            ], 500);
        }

        $tenant = $this->getTenant();

        $misidentified = $tenant->misidentifiedPackages()->create([
            'client_id' => $request->client_id,
            'trackings' => $request->trackings,
            'cargo_entry_id' => $request->cargo_entry_id,
            'branch_to' => $request->branch_to,
        ]);

        if ($misidentified) {
            // notify
            return response()->json([
                'msg' => __('Success'),
                'error' => false,
            ], 200);
        }

        return response()->json([
            'msg' => __('Error'),
            'error' => true,
        ], 500);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($domain, $id)
    {
        $tenant = $this->getTenant();
        $user = auth()->user();
        $misidentified = $tenant->misidentifiedPackages();

        if (!$user->isSuperAdmin() && !$user->isWarehouse()) {
            $misidentified = $misidentified->where('branch_to', $user->currentBranch()->id);
        }

        return view('tenant.misidentified-package.show', [
            'misidentified_package' => $misidentified->with('toBranch')->findOrFail($id),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    private function validates($request, $extraRules = [])
    {
        $rules = [
            'g-recaptcha-response' => 'required|captcha',
            'trackings' => 'required',
            'branch_to' => 'required',
            'client_id' => 'sometimes|integer',
            'cargo_ebtry_id' => 'sometimes|integer',
        ];

        return Validator::make($request->all(), array_merge($rules, $extraRules));
    }
}
