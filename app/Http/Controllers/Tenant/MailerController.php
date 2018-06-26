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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(MailerRequest $request)
    {
        $tenant = $this->getTenant();

        foreach ($request->mailers as $inputs) {
            $mailer = new Fluent($inputs);
            
            $tenant->mailers()->updateOrCreate(
                ['id' => $mailer->mid ],
                [
                    'name' => $mailer->name,
                    'status' => $mailer->status,
                    'description' => $mailer->description,
                    'vol_price' => $mailer->vol_price,
                    'real_price' => $mailer->real_price,
                ]
            );
        }

        return redirect()->route('tenant.mailer.list', $tenant->domain)
                ->with('flash_success', __('The :what has been created.', ['what' => __('Mailers') ]));
    }
}
