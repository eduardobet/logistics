<?php

namespace Logistics\Http\Controllers\Tenant\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Logistics\Http\Controllers\Controller;
use Logistics\Events\Tenant\CompanyLogoAdded;
use Logistics\Http\Requests\Tenant\CompanyRequest;

class CompanyController extends Controller
{
    public function edit()
    {
    	return view('tenant.company.edit');
    }

    public function update(CompanyRequest $request)
    {
    	$company = auth()->user()->company;

    	$company->name = $request->name;
    	$company->telephones = $request->telephones;
    	$company->emails = $request->emails;
    	$company->ruc = $request->ruc;
    	$company->dv = $request->dv;
    	$company->address = $request->address;
    	$company->lang = $request->lang;

    	if($company->save()) {
    		view()->share([
                'tenant' => $company,
            ]);

            $this->uploadLogo($request, $company);

    		return redirect()->route('tenant.admin.company.edit')
    			->with('flash_success', __('The company has been updated.'));
    	}
    }

    protected function uploadLogo($request, $tenant)
    {
        if ($request->hasFile('logo')) {

            if (Storage::disk('public')->exists($tenant->logo)) {
                Storage::disk('public')->delete($tenant->logo);
            }
            
            $tenant->update([
                'logo' => $request->logo->store("tenant/{$tenant->id}/images/logos", 'public'),
            ]);

            event(new CompanyLogoAdded($tenant));
        }
    }
}
