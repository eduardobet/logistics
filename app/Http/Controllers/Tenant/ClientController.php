<?php

namespace Logistics\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
use Logistics\DB\Tenant\Zone;
use Logistics\DB\Tenant\Country;
use Logistics\DB\Tenant\Department;
use Logistics\Http\Controllers\Controller;
use Logistics\Http\Requests\Tenant\ClientRequest;
use Logistics\Events\Tenant\ClientWasCreatedEvent;

class ClientController extends Controller
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

        $clients = $tenant->clients()
            ->with('branch');

        if (!auth()->user()->isSuperAdmin()) {
            $clients = $clients->where('branch_id', auth()->user()->currentBranch()->id);
        }

        $searching = 'N';

        if (auth()->user()->isSuperAdmin() && $branch = request('branch_id')) {
            $clients = $clients->where('branch_id', $branch);
            $searching = 'Y';
        }

        $clients = $clients->paginate(15);

        $branches = $this->getBranches();

        if (!auth()->user()->isSuperAdmin()) {
            $branches = $branches->where('id', auth()->user()->currentBranch()->id);
        }

        return view('tenant.client.index', [
            'clients' => $clients,
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
        $user = auth()->user();
        $data = [
            'countries' => (new Country())->getCountryAsList($this->getTenantId()),
        ];

        if ($user->isSuperAdmin() || $user->isAdmin()) {
            $data['branches'] = $this->getBranches();
        }

        return view('tenant.client.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Logistics\Http\Requests\Tenant\ClientRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ClientRequest $request)
    {
        $tenant = $this->getTenant();

        $client = new \Logistics\DB\Tenant\Client;

        if ($tenant->migration_mode && $request->manual_id) {
            $client->manual_id = $request->manual_id;
        } else {
            $max = $client->where('tenant_id', $tenant->id)
                ->where('branch_id', $request->branch_id)
                ->max('manual_id');

            if (!$max) {
                $max = 0;
            }

            $client->manual_id = $max + 1;
        }

        $client->tenant_id = $tenant->id;
        $client->created_by_code = auth()->id();
        $client->first_name = $request->first_name;
        $client->last_name = $request->last_name;
        $client->full_name = $request->first_name . ' ' . $request->last_name;
        $client->pid = $request->pid;
        $client->email = $request->email;
        $client->telephones = $request->telephones;
        $client->type = $request->type;
        $client->org_name = $request->org_name;
        $client->status = $request->status;
        $client->branch_id = $request->branch_id;

        // optionals
        $client->country_id = $request->country_id;
        $client->department_id = $request->department_id;
        $client->city_id = $request->city_id;
        $client->address = $request->address;
        $client->notes = $request->notes;
        $client->pay_volume = $request->has('pay_volume');
        $client->special_rate = $request->has('special_rate');
        $client->special_maritime = $request->has('special_maritime');
        $client->pay_first_lbs_price = $request->has('pay_first_lbs_price');
        $client->pay_extra_maritime_price = $request->has('pay_extra_maritime_price');
        $client->vol_price = $request->vol_price ? $request->vol_price : null;
        $client->real_price = $request->real_price ? $request->real_price : null;
        $client->first_lbs_price = $request->first_lbs_price ? $request->first_lbs_price : null;
        $client->maritime_price = $request->maritime_price ? $request->maritime_price : null;
        $client->extra_maritime_price = $request->extra_maritime_price ? $request->extra_maritime_price : null;

        $saved = $client->save();

        if ($saved) {
            $client->fresh()->genBox($request->branch_id, $request->branch_code, $request->branch_initial);

            if ($tenant->email_allowed_dup !== $request->email) {
                dispatch(new \Logistics\Jobs\Tenant\SendClientWelcomeEmail($tenant, $client));
            }

            $client->saveExtraContacts($request, $tenant->id);

            return redirect()->route('tenant.client.list', $tenant->domain)
                ->with('flash_success', __('The :what has been created.', ['what' => __('Client') ]));
        }

        return redirect()->route('tenant.client.create', $tenant->domain)
            ->withInput()
            ->with('flash_error', __('Error while trying to :action :what', [
                'action' => __('Save'),
                'what' => __('The client'),
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
        $client = $this->getTenant()->clients();

        if (!auth()->user()->isSuperAdmin()) {
            $client = $client->where('branch_id', auth()->user()->currentBranch()->id);
        }

        $client = $client->with(['branch', 'creator', 'editor'])->findOrFail($id);

        return view('tenant.client.show', [
            'client' => $client,
            'countries' => (new Country())->getCountryAsList($this->getTenantId()),
            'departments' => (new Department())->getDepartmentAsList($client->country_id),
            'zones' => (new Zone())->getZoneAsList($client->department_id),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string  $tenant
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($tenant, $id)
    {
        $client = $this->getTenant()->clients();

        if (!auth()->user()->isSuperAdmin()) {
            $client = $client->where('branch_id', auth()->user()->currentBranch()->id);
        }

        $client = $client->with(['branch','creator', 'editor'])->findOrFail($id);

        return view('tenant.client.edit', [
            'client' => $client,
            'countries' => (new Country())->getCountryAsList($this->getTenantId()),
            'departments' => (new Department())->getDepartmentAsList($client->country_id),
            'zones' => (new Zone())->getZoneAsList($client->department_id),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Logistics\Http\Requests\Tenant\ClientRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ClientRequest $request, $tenant, $id)
    {
        $tenant = $this->getTenant();
        $client = $tenant->clients();

        if (!auth()->user()->isSuperAdmin()) {
            $client = $client->where('branch_id', auth()->user()->currentBranch()->id);
        }

        $client = $client->findOrFail($id);

        $oldEmail = $client->email;

        $client->updated_by_code = auth()->id();
        $client->first_name  = $request->first_name;
        $client->last_name  = $request->last_name;
        $client->full_name = $request->first_name . ' ' . $request->last_name;
        $client->pid  = $request->pid;
        $client->email  = $request->email;
        $client->telephones  = $request->telephones;
        $client->type  = $request->type;
        $client->org_name  = $request->org_name;
        $client->status  = $request->status;

        // optionals
        $client->country_id         = $request->country_id;
        $client->department_id  = $request->department_id;
        $client->city_id  = $request->city_id;
        $client->address  = $request->address;
        $client->notes  = $request->notes;
        $client->pay_volume  = $request->has('pay_volume');
        $client->special_rate  = $request->has('special_rate');
        $client->special_maritime  = $request->has('special_maritime');
        $client->pay_first_lbs_price  = $request->has('pay_first_lbs_price');
        $client->pay_extra_maritime_price  = $request->has('pay_extra_maritime_price');
        $client->vol_price = $request->vol_price ? $request->vol_price : null;
        $client->real_price = $request->real_price ? $request->real_price : null;
        $client->first_lbs_price = $request->first_lbs_price ? $request->first_lbs_price : null;
        $client->maritime_price = $request->maritime_price ? $request->maritime_price : null;
        $client->extra_maritime_price = $request->extra_maritime_price ? $request->extra_maritime_price : null;

        if ($oldEmail !== $request->email) {
            $client->email = $request->email;
        }

        $updated = $client->save();

        if ($updated) {
            if ($oldEmail !== $request->email) {
                if ($tenant->email_allowed_dup !== $request->email) {
                    dispatch(new \Logistics\Jobs\Tenant\SendClientWelcomeEmail($tenant, $client));
                }
            }

            $client->saveExtraContacts($request, $tenant->id);

            return redirect()->route('tenant.client.list', $tenant->domain)
                ->with('flash_success', __('The :what has been updated.', ['what' => __('Client') ]));
        }

        return redirect()->route('tenant.client.edit', [$tenant->domain, $id])
            ->withInput()
            ->with('flash_error', __('Error while trying to :action :what', [
                'action' => __('Update'),
                'what' => __('The client'),
            ]));
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

    public function econtactTmpl()
    {
        return response()->json([
            'view' => view('tenant.client.extra-contacts')->render(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteExtraContact(Request $request)
    {
        $tenant = $this->getTenant();
        $client =$tenant->clients();

        if (!auth()->user()->isSuperAdmin()) {
            $client = $client->where('branch_id', auth()->user()->currentBranch()->id);
        }

        $client = $client->find($request->client_id);

        if (!$client) {
            return response()->json(['error' => true, 'msg' => __('Not Found.'), ], 404);
        }

        $econtact = $client->extraContacts()->find($request->id);

        if (!$econtact) {
            return response()->json(['error' => true, 'msg' => __('Not Found.'), ], 404);
        }

        $deleted = $econtact->delete();

        if ($deleted) {
            return response()->json(['error' => false, 'msg' => __('Deleted successfully'), ]);
        }

        return response()->json(['error' => false, 'msg' =>
            __('Error while trying to :action :what', [
                'action' => __('Delete'),
                'what' => __('The extra contact'),
            ])
        , ]);
    }

    public function resentWelcomeEmail()
    {
        $tenant = $this->getTenant();
        $client = $tenant->clients();

        if (!auth()->user()->isSuperAdmin()) {
            $client = $client->where('branch_id', auth()->user()->currentBranch()->id);
        }

        $client = $client->find(request()->client_id);

        if (!$client) {
            return response()->json(['error' => true, 'msg' => __('Not Found.'), ], 404);
        }

        dispatch(new \Logistics\Jobs\Tenant\SendClientWelcomeEmail($tenant, $client));

        return response()->json(['error' => false, 'msg' => __('Success'), ]);
    }
}
