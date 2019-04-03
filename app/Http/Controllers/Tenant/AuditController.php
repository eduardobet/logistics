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

        return view('tenant.audit.index', [
            'audits' => $tenant->audits->load(['user'])
        ]);
    }

    public function show()
    {
        $tenant = $this->getTenant();

        return view('tenant.audit.show', [
            'audits' => $tenant->audits->load(['user'])
        ]);
    }
}
