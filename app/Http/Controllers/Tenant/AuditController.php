<?php

namespace Logistics\Http\Controllers\Tenant;

use Logistics\Traits\Tenant;
use Logistics\Http\Controllers\Controller;

class AuditController extends Controller
{
    use Tenant;

    public function index()
    {
        $tenant = $this->getTenant();

        dd(\Logistics\DB\Tenant\Payment::find(35)->audits);

        return view('tenant.audit.index', [
            'audits' => $tenant->audits->load(['user'])
        ]);
    }
}
