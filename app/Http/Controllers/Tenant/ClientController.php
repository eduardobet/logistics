<?php

namespace Logistics\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
use Logistics\Http\Controllers\Controller;
use Logistics\Http\Requests\Tenant\ClientRequest;
use Logistics\Events\Tenant\ClientWasCreatedEvent;
use Logistics\DB\Tenant\Country;

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

        $clients = $tenant->clients()->with('boxes')->paginate(15);

        return view('tenant.client.index', [
            'clients' => $clients,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('tenant.client.create', [
            'countries' => (new Country())->getCountryAsList($this->getTenantId()),
        ]);
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
        
        $client = $tenant->clients()->create([
            "created_by_code" => auth()->id(),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'pid' => $request->pid,
            'email' => $request->email,
            'telephones' => $request->telephones,
            'type' => $request->type,
            'org_name' => $request->org_name,
            'status' => $request->status,

            // optionals
            'country_id' => $request->country_id,
            'department_id' => $request->department_id,
            'city_id' => $request->city_id,
            'notes' => $request->notes,
            'pay_volume' => $request->has('pay_volume'),
            'special_rate' => $request->has('special_rate'),
            'special_maritime' => $request->has('special_maritime'),
        ]);

        if ($client) {
            $client->genBox($request->branch_id, $request->branch_code);

            event(new ClientWasCreatedEvent($tenant, $client));

            return redirect()->route('tenant.client.list')
                ->with('flash_success', __('The client has been created.'));
        }

        return redirect()->route('tenant.client.create')
            ->withInput()
            ->with('flash_error', __('Error while trying to :action :what', [
                'action' => __('Save'),
                'what' => __('The client'),
            ]));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $client = $this->getTenant()->clients()->with('boxes')->findOrFail($id);

        return view('tenant.client.edit', [
            'client' => $client,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Logistics\Http\Requests\Tenant\ClientRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ClientRequest $request, $id)
    {
        $tenant = $this->getTenant();
        $client =$tenant->clients()->findOrFail($id);
        $oldEmail = $client->email;

        $client->updated_by_code = auth()->id();
        $client->first_name  = $request->first_name;
        $client->last_name  = $request->last_name;
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
        $client->notes  = $request->notes;
        $client->pay_volume  = $request->has('pay_volume');
        $client->special_rate  = $request->has('special_rate');
        $client->special_maritime  = $request->has('special_maritime');

        if ($oldEmail !== $request->email) {
            $client->email = $request->email;
        }

        $updated = $client->save();

        if ($updated) {
            if ($oldEmail !== $request->email) {
                event(new ClientWasCreatedEvent($tenant, $client));
            }

            return redirect()->route('tenant.client.list')
                ->with('flash_success', __('The client has been updated.'));
        }

        return redirect()->route('tenant.client.edit', $id)
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
}
