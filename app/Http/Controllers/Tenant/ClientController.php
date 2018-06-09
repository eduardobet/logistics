<?php

namespace Logistics\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
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
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('tenant.client.create');
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
            'pay_volume' => $request->pay_volume,
            'special_rate' => $request->special_rate,
            'special_maritime' => $request->special_maritime,
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
}
