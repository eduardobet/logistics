<?php

namespace Logistics\Http\Controllers\Tenant\Admin;

use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
use Logistics\Http\Controllers\Controller;
use Logistics\Http\Requests\Tenant\BranchRequest;

class BranchController extends Controller
{
    use Tenant;

    public function index()
    {
    	//
    }

    public function create()
    {
    	return view('tenant.branch.create');
    }

    public function store(BranchRequest $request)
    {
    	$branch = $this->getTenant()->branches()->create([
    		'created_by_code' => auth()->id(),
    		'name' => $request->name,
    		'address' => $request->address,
    		'telephones' => $request->telephones,
    		'faxes' => $request->faxes,
    		'emails' => $request->emails,
    		'code' => $request->code,
    		'status' => $request->status,
    		'lat' => $request->lat,
    		'lng' => $request->lng,
    		'ruc' => $request->ruc,
    		'dv' => $request->dv,
    	]);

    	if($branch) {
    		return redirect()->route('tenant.admin.branch.list')
    			->with('flash_success', __('The branch has been created.'));
    	}

    	return redirect()->route('tenant.admin.branch.create')
    			->withInput()
    			->with('flash_error', __('Error while trying to :action :what', [
    				'action' => __('Save'),
    				'what' => __('The branch'),
    			]));
    }

    public function edit($id)
    {
    	$branch = $this->getTenant()->branches()->findOrFail($id);

    	return view('tenant.branch.edit', compact('branch'));
    }

    public function update(BranchRequest $request)
    {
    	$branch = $this->getTenant()->branches()->findOrFail($request->id);

    	$branch->updated_by_code = auth()->id();
		$branch->name = $request->name;
		$branch->address = $request->address;
		$branch->telephones = $request->telephones;
		$branch->faxes = $request->faxes;
		$branch->emails = $request->emails;
		$branch->code = $request->code;
		$branch->status = $request->status;
		$branch->lat = $request->lat;
		$branch->lng = $request->lng;
		$branch->ruc = $request->ruc;
		$branch->dv = $request->dv;

		$updated = $branch->save();

		if($updated) {
    		return redirect()->route('tenant.admin.branch.list')
    			->with('flash_success', __('The branch has been updated.'));
    	}

    	return redirect()->route('tenant.admin.branch.edit', $request->id)
    			->withInput()
    			->with('flash_error', __('Error while trying to :action :what', [
    				'action' => __('Update'),
    				'what' => __('The branch'),
    			]));
    }
}
