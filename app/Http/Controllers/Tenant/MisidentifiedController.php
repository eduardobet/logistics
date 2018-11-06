<?php

namespace Logistics\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
use Illuminate\Support\Facades\Validator;
use Logistics\Http\Controllers\Controller;

class MisidentifiedController extends Controller
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
        return view('tenant.misidentified-package.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->validates($request);

        if ($validator->fails()) {
            return response()->json([
                'msg' => $validator->errors()->first(),
                'error' => true,
            ], 500);
        }

        $tenant = $this->getTenant();

        $misidentified = $tenant->misidentifiedPackages()->create([
            'client_id' => $request->client_id,
            'trackings' => $request->trackings,
            'cargo_entry_id' => $request->cargo_entry_id,
            'branch_to' => $request->branch_to,
        ]);

        if ($misidentified) {
            // notify
            return response()->json([
                'msg' => __('Success'),
                'error' => false,
            ], 200);
        }

        return response()->json([
            'msg' => __('Error'),
            'error' => true,
        ], 500);
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

    private function validates($request, $extraRules = [])
    {
        $rules = [
            'g-recaptcha-response' => 'required|captcha',
            'trackings' => 'required',
            'branch_to' => 'required',
        ];

        return Validator::make($request->all(), array_merge($rules, $extraRules));
    }
}
