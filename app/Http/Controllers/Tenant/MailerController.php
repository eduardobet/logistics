<?php

namespace Logistics\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use Logistics\Traits\Tenant;
use Illuminate\Support\Fluent;
use Logistics\Http\Controllers\Controller;
use Logistics\Http\Requests\Tenant\MailerRequest;

class MailerController extends Controller
{
    use Tenant;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $mailers = $this->getTenant()->mailers();

        if ($filter = request('filter')) {
            if (is_numeric($filter)) {
                $mailers = $mailers->where('id', $filter);
            } else {
                $mailers = $mailers->where('name', 'like', "%{$filter}%");
            }
        }
        
        $mailers = $mailers->paginate(15);

        return view('tenant.mailer.index', compact('mailers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('tenant.mailer.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Logistics\Http\Requests\Tenant\MailerRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(MailerRequest $request, $tenant)
    {
        $this->__updateOrCreate($request);

        return redirect()->route('tenant.mailer.list', $tenant)
                ->with('flash_success', __('The :what has been created.', ['what' => __('Mailers') ]));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($tenant, $id)
    {
        $tenant = $this->getTenant();

        return view('tenant.mailer.edit', [
            'mailers' => $tenant->mailers,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Logistics\Http\Requests\Tenant\MailerRequest $request
     * @return \Illuminate\Http\Response
     */
    public function update(MailerRequest $request, $tenant)
    {
        $this->__updateOrCreate($request);
        $this->__updateOrCreate($request);

        return redirect()->route('tenant.mailer.list', $tenant)
            ->with('flash_success', __('The :what has been updated.', ['what' => __('Mailers')]));
    }

    private function __updateOrCreate($request)
    {
        $tenant = $this->getTenant();

        foreach ($request->mailers as $inputs) {
            $mailer = new Fluent($inputs);

            $tenant->mailers()->updateOrCreate(
                ['id' => $mailer->mid],
                [
                    'name' => $mailer->name,
                    'status' => $mailer->status,
                    'description' => $mailer->description,
                    'vol_price' => $mailer->vol_price ? : 0.0,
                    'real_price' => $mailer->real_price ? : 0.0,
                ]
            );
        }
    }

    public function getTmpl($tenant)
    {
        return response()->json([
            'view' => view('tenant.mailer.mailer-tmpl')->render(),
        ]);
    }

    public function destroy($tenant)
    {
        $tenant = $this->getTenant();
        $mailer = $tenant->mailers()->find(request('id'));

        if (!$mailer) {
            return response()->json(['error' => true, 'msg' => __('Not Found.'), ], 404);
        }

        $deleted = $mailer->delete();

        if ($deleted) {
            return response()->json(['error' => false, 'msg' => __('Deleted successfully'), ]);
        }

        return response()->json([
            'error' => false, 'msg' =>
                __('Error while trying to :action :what', [
                'action' => __('Delete'),
                'what' => __('The mailer'),
            ]),
        ]);
    }
}
