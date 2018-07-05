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
        $branches = $this->getTenant()->branches();

        if ($filter = request('filter')) {
            if (is_numeric($filter)) {
                $branches = $branches->where('id', $filter);
            } else {
                $branches = $branches->where('name', 'like', "%{$filter}%");
            }
        }
        
        $branches = $branches->paginate(15);

        return view('tenant.branch.index', compact('branches'));
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
            'direct_comission' => $request->has('direct_comission'),
            'should_invoice' => $request->has('should_invoice'),
            'vol_price' => $request->vol_price,
            'real_price' => $request->real_price,
            'dhl_price' => $request->dhl_price,
            'maritime_price' => $request->maritime_price,
        ]);

        if ($branch) {
            return redirect()->route('tenant.admin.branch.list', $request->domain)
                ->with('flash_success', __('The :what has been created.', ['what' => __('Branch') ]));
        }

        return redirect()->route('tenant.admin.branch.create', $request->domain)
                ->withInput()
                ->with('flash_error', __('Error while trying to :action :what', [
                    'action' => __('Save'),
                    'what' => __('The branch'),
                ]));
    }

    public function edit($domain, $id)
    {
        $branchData = $this->getTenant()->branches()->findOrFail($id);

        return view('tenant.branch.edit', compact('branchData'));
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
        $branch->direct_comission = $request->has('direct_comission');
        $branch->should_invoice = $request->has('should_invoice');

        $branch->real_price = $request->real_price;
        $branch->vol_price = $request->vol_price;
        $branch->dhl_price = $request->dhl_price;
        $branch->maritime_price = $request->maritime_price;

        $updated = $branch->save();

        if ($updated) {
            return redirect()->route('tenant.admin.branch.list', $request->domain)
                ->with('flash_success', __('The :what has been updated.', ['what' => __('Branch')]));
        }

        return redirect()->route('tenant.admin.branch.edit', [$request->domain, $request->id])
                ->withInput()
                ->with('flash_error', __('Error while trying to :action :what', [
                    'action' => __('Update'),
                    'what' => __('The branch'),
                ]));
    }
}
