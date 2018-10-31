<?php

namespace Logistics\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
use Illuminate\Support\Facades\Validator;
use Logistics\Http\Controllers\Controller;

class TrackingController extends Controller
{
    use Tenant;

    public function showTrackingForm()
    {
        return view('tenant.tracking.index');
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function track(Request $request)
    {
        $validator = $this->validates($request);

        if ($validator->fails()) {
            return response()->json([
                'msg' => $validator->errors()->first(),
                'error' => true,
            ], 500);
        }

        $tenant = $this->getTenant();

        $recas = $tenant->cargoEntries()
            ->with('branch')
            ->where('trackings', 'like', "%$request->term%")
            ->orderBy('id')
            ->get();
            
        return response()->json([
            'error' => false,
            'misindentified' => ($mReca = $recas->where('type', 'M')->first()) != null,
            'data' => [
                'recas' => $recas,
                'mReca' => $mReca,
                'last_wh' => $lastWh = $tenant->warehouses()
                    ->with(['toBranch', 'invoice' => function ($invoice) {
                        $invoice->with('payments')->orderBy('id', 'DESC');
                    }])
                    ->where('trackings', 'like', "%$request->term%")->get()->last(),
            ]
        ], 200);
    }

    private function validates($request, $extraRules = [])
    {
        $rules = [
            'g-recaptcha-response' => 'required|captcha',
            'term' => 'required',
        ];

        return Validator::make($request->all(), array_merge($rules, $extraRules));
    }
}
