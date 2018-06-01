<?php

namespace Logistics\Http\Controllers\Tenant\Admin;

use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
use Logistics\Http\Controllers\Controller;
use Logistics\Events\Tenant\EmployeeWasCreatedEvent;
use Logistics\Http\Requests\Tenant\EmployeeCreationRequest;

class EmployeeController extends Controller
{
    use Tenant;

    public function create()
    {
    }

    public function store(EmployeeCreationRequest $request)
    {
    	$tenant = $this->getTenant();

    	$employee = $tenant->employees()->create([
    		'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'type' => $request->type,
            'status' => 'L',
    	]);

    	$employee->branches()->sync($request->branches);

    	event(new EmployeeWasCreatedEvent($tenant, $employee));

    	return redirect()->route('tenant.admin.employee.list')
    		->with('flash_success', __('The employee has been created.'));
    }
}
