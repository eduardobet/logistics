<?php

namespace Logistics\Http\Controllers\Tenant\Admin;

use Logistics\DB\User;
use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
use Logistics\Http\Controllers\Controller;
use Logistics\Jobs\Tenant\SendEmployeeWelcomeEmail;
use Logistics\Http\Requests\Tenant\EmployeeCreationRequest;

class EmployeeController extends Controller
{
    use Tenant;

    public function index()
    {
        $employees = $this->getTenant()->employees()->with('branches');
        $searching = 'N';
        $user = auth()->user();

        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
            $employees = $employees->whereHas('branches', function ($query) use ($user) {
                $query->where('id', $user->currentBranch()->id);
            });
        }


        if ($filter = request('filter')) {
            if (is_numeric($filter)) {
                $employees = $employees->where('id', $filter);
            } else {
                $employees = $employees->where('full_name', 'like', "%{$filter}%");
            }

            $searching = 'Y';
        }

        if ((($user->isSuperAdmin() || $user->isAdmin()) || $user->isWarehouse()) && $branch = request('branch_id')) {
            $employees = $employees->whereHas('branches', function ($query) use ($branch) {
                $query->where('id', $branch);
            });
            
            $searching = 'Y';
        }

        $employees = $employees->paginate(15);

        $branches = $this->getBranches();

        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->isWarehouse()) {
            $branches = $branches->where('id', $user->currentBranch()->id);
        }

        return view('tenant.employee.index', [
            'branches' => $branches,
            'employees' => $employees,
            'searching' => $searching,
        ]);
    }

    public function create()
    {
        return view('tenant.employee.create', [
            'positions' => $this->positions(),
            'permissions' => $this->permissions(),
            'branches' => $this->getBranches(),
            'employee' => new User(),
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
            'pid' => $request->pid,
            'telephones' => $request->telephones,
            'position' => $request->position,
            'address' => $request->address,
            'notes' => $request->notes,
            'created_by_code' => auth()->id(),
            'permissions' => $request->permissions && is_array($request->permissions) ? $request->permissions : [],
            'password' => $request->password ? bcrypt($request->password) : null,
            'status' => $request->password ? 'A' : 'L',
        ]);

        if ($employee) {
            $employee->branches()->sync($request->branches);

            if (!$request->password) {
                dispatch(new SendEmployeeWelcomeEmail($tenant, $employee));
            }

            $employee->branchesForInvoice()->sync($request->branches_for_invoices);

            return redirect()->route('tenant.admin.employee.list', $request->domain)
                ->with('flash_success', __('The :what has been created.', ['what' => __('Employee') ]));
        }

        return redirect()->route('tenant.admin.employee.create', $request->domain)
            ->withInput()
            ->with('flash_error', __('Error while trying to :action :what', [
                'action' => __('Save'),
                'what' => __('The employee'),
            ]));
    }

    public function edit($domain, $id)
    {
        $tenant = $this->getTenant();
        $employee = $tenant->employees()->with('branches')->where('id', $id)->firstOrFail();

        return view('tenant.employee.edit', [
            'employee' => $employee,
            'positions' => $this->positions(),
            'permissions' => $this->permissions(),
            'branches' => $this->getBranches(),
        ]);
    }

    public function update(EmployeeCreationRequest $request)
    {
        $tenant = $this->getTenant();

        $employee = $tenant->employees()->where('id', $request->id)->first();
        $oldEmail = $employee->email;

        $employee->first_name = $request->first_name;
        $employee->last_name  = $request->last_name;
        $employee->email  = $request->email;
        $employee->type  = $request->type;
        $employee->status = $request->password ? 'A' : $request->status;
        $employee->is_main_admin = $request->has('is_main_admin');
        $employee->full_name = $request->first_name . ' ' . $request->last_name;
        $employee->pid = $request->pid;
        $employee->position = $request->position;
        $employee->telephones = $request->telephones;
        $employee->notes = $request->notes;
        $employee->address = $request->address;
        $employee->updated_by_code = auth()->id();
        $employee->permissions = $request->permissions && is_array($request->permissions) ? $request->permissions : [];
        
        if ($request->password) {
            $employee->password = bcrypt($request->password);
        }

        $updated = $employee->update();

        $employee->branches()->sync($request->branches);
        $employee->branchesForInvoice()->sync($request->branches_for_invoices);

        if ($updated) {
            if ($oldEmail !== $request->email && !$request->password) {
                dispatch(new SendEmployeeWelcomeEmail($tenant, $employee));
            }

            return redirect()->route('tenant.admin.employee.list', $request->domain)
                ->with('flash_success', __('The :what has been updated.', ['what' => __('Employee')]));
        }

        return redirect()->route('tenant.admin.employee.edit', [$request->domain, $request->id])
            ->withInput()
            ->with('flash_error', __('Error while trying to :action :what', [
                'action' => __('Update'),
                'what' => __('The employee'),
            ]));
    }

    public function resentWelcomeEmail()
    {
        $tenant = $this->getTenant();
        $employee = $tenant->employees()->where('id', request()->employee_id)->first();

        if (!$employee) {
            return response()->json(['error' => true, 'msg' => __('Not Found.'), ], 404);
        }

        dispatch(new SendEmployeeWelcomeEmail($tenant, $employee));

        return response()->json(['error' => false, 'msg' => __('Success'), ]);
    }
}
