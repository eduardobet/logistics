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

    public function index()
    {
        $employees = $this->getTenant()->employees()->with('branches');

        if ($filter = request('filter')) {
            if (is_numeric($filter)) {
                $employees = $employees->where('id', $filter);
            } else {
                $employees = $employees->where('full_name', 'like', "%{$filter}%");
            }
        }

        $employees = $employees->paginate(15);

        return view('tenant.employee.index', compact('employees'));
    }

    public function create()
    {
        return view('tenant.employee.create', [
            'positions' => $this->positions(),
            'permissions' => $this->permissions(),
        ]);
    }

    public function store(EmployeeCreationRequest $request)
    {
        $tenant = $this->getTenant();

        $employee = $tenant->employees()->create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'type' => $request->type,
            'is_main_admin' => $request->has('is_main_admin'),
            'full_name' => $request->first_name . ' ' . $request->last_name,
            'status' => 'L',
            'pid' => $request->pid,
            'telephones' => $request->telephones,
            'position' => $request->position,
            'address' => $request->address,
            'notes' => $request->notes,
            'created_by_code' => auth()->id(),
            'permissions' => $request->permissions && is_array($request->permissions) ? $request->permissions : [],
        ]);

        if ($employee) {
            $employee->branches()->sync($request->branches);

            event(new EmployeeWasCreatedEvent($tenant, $employee));

            return redirect()->route('tenant.admin.employee.list')
                ->with('flash_success', __('The employee has been created.'));
        }

        return redirect()->route('tenant.admin.employee.create')
            ->withInput()
            ->with('flash_error', __('Error while trying to :action :what', [
                'action' => __('Save'),
                'what' => __('The employee'),
            ]));
    }

    public function edit($id)
    {
        $tenant = $this->getTenant();
        $employee = $tenant->employees()->where('id', $id)->firstOrFail();

        return view('tenant.employee.edit', [
            'employee' => $employee,
            'positions' => $this->positions(),
            'permissions' => $this->permissions(),
        ]);
    }

    public function update(EmployeeCreationRequest $request)
    {
        $tenant = $this->getTenant();

        $employee = $tenant->employees()->where('id', $request->id)->first();

        $employee->first_name = $request->first_name;
        $employee->last_name  = $request->last_name;
        $employee->email  = $request->email;
        $employee->type  = $request->type;
        $employee->status = $request->status;
        $employee->is_main_admin = $request->has('is_main_admin');
        $employee->full_name = $request->first_name . ' ' . $request->last_name;
        $employee->pid = $request->pid;
        $employee->position = $request->position;
        $employee->telephones = $request->telephones;
        $employee->notes = $request->notes;
        $employee->address = $request->address;
        $employee->updated_by_code = auth()->id();
        $employee->permissions = $request->permissions && is_array($request->permissions) ? $request->permissions : [];

        $updated = $employee->update();

        $employee->branches()->sync($request->branches);

        if ($updated) {
            return redirect()->route('tenant.admin.employee.list')
                ->with('flash_success', __('The employee has been updated.'));
        }

        return redirect()->route('tenant.admin.employee.edit', $request->id)
            ->withInput()
            ->with('flash_error', __('Error while trying to :action :what', [
                'action' => __('Update'),
                'what' => __('The employee'),
            ]));
    }
}
