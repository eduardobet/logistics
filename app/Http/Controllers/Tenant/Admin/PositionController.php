<?php

namespace Logistics\Http\Controllers\Tenant\Admin;

use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
use Logistics\Http\Controllers\Controller;
use Logistics\Http\Requests\Tenant\PositionRequest;

class PositionController extends Controller
{
    use Tenant;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $positions = $this->getTenant()->positions();

        if ($filter = request('filter')) {
            if (is_numeric($filter)) {
                $positions = $positions->where('id', $filter);
            } else {
                $positions = $positions->where('name', 'like', "%{$filter}%");
            }
        }

        $positions = $positions->paginate(15);

        return view('tenant.position.index', compact('positions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('tenant.position.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Logistics\Http\Requests\Tenant\PositionRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(PositionRequest $request)
    {
        $tenant = $this->getTenant();

        $position = $tenant->positions()->create([
            'created_by_code' => auth()->id(),
            'name' => $request->name,
            'status' => $request->status,
            'description' => $request->description,
        ]);

        if ($position) {
            return redirect()->route('tenant.admin.position.list', $request->domain)
                ->with('flash_success', __('The :what has been created.', ['what' => __('Position')]));
        }

        return redirect()->route('tenant.admin.position.create', $request->domain)
            ->withInput()
            ->with('flash_error', __('Error while trying to :action :what', [
                'action' => __('Save'),
                'what' => __('The position'),
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
    public function edit($domain, $id)
    {
        $position = $this->getTenant()->positions()->findOrFail($id);

        return view('tenant.position.edit', compact('position'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Logistics\Http\Requests\Tenant\PositionRequest $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PositionRequest $request, $id)
    {
        $position = $this->getTenant()->positions()->findOrFail($request->id);

        $position->updated_by_code = auth()->id();
        $position->name = $request->name;
        $position->status = $request->status;
        $position->description = $request->description;

        $updated = $position->save();

        if ($updated) {
            return redirect()->route('tenant.admin.position.list', $request->domain)
                ->with('flash_success', __('The :what has been updated.', ['what' => __('Position')]));
        }

        return redirect()->route('tenant.admin.position.edit', [$request->domain, $request->id])
            ->withInput()
            ->with('flash_error', __('Error while trying to :action :what', [
                'action' => __('Update'),
                'what' => __('The position'),
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
