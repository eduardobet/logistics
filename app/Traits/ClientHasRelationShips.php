<?php

namespace Logistics\Traits;

use Illuminate\Support\Fluent;

trait ClientHasRelationShips
{
    public function boxes()
    {
        return $this->hasMany(\Logistics\DB\Tenant\Box::class);
    }

    public function branch()
    {
        return $this->belongsTo(\Logistics\DB\Tenant\Branch::class);
    }

    public function extraContacts()
    {
        return $this->hasMany(\Logistics\DB\Tenant\ExtraContact::class);
    }

    public function clientInvoices()
    {
        return $this->hasMany(\Logistics\DB\Tenant\Invoice::class);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function tenant()
    {
        return $this->belongsTo(\Logistics\DB\Tenant\Tenant::class);
    }

    public function scopeWithAndWhereHas($query, $relation, $constraint)
    {
        return $query->whereHas($relation, $constraint)
            ->with([$relation => $constraint]);
    }

    public function saveExtraContacts($request, $tenantId)
    {
        if ($request->econtacts && is_array($request->econtacts)) {
            foreach ($request->econtacts as $key => $econtact) {
                $econtact = new Fluent($econtact);
                
                if (trim($econtact->efull_name)) {
                    $this->extraContacts()->updateOrCreate([
                        'tenant_id' => $tenantId,
                        'client_id' => $this->id,
                        'id' => $econtact->eid
                    ], [
                        'full_name' => $econtact->efull_name,
                        'pid' => $econtact->epid,
                        'email' => $econtact->eemail,
                        'telephones' => $econtact->etelephones,
                        'tenant_id' => $tenantId,
                    ]);
                }
            }
        }
    }

    public function genBox($branchId, $branchCode, $branchInitial)
    {
        $boxes = $this->boxes()->active()->get();

        if ($boxes->count()) {
            foreach ($boxes as $box) {
                $box->update([
                    'status' => 'I',
                ]);
            }
        }

        $this->boxes()->create([
            'tenant_id' => $this->tenant_id,
            'status' => 'A',
            'branch_id' => $branchId,
            'branch_code' => $branchCode,
            'branch_initial' => $branchInitial,
        ]);
    }

    public function getManualIdDspAttribute()
    {
        return str_pad($this->manual_id, 2, '0', STR_PAD_LEFT);
    }

    public function getClientsByBranch($branchId)
    {
        $tenant = $this->getTenant();

        return $tenant->clients()
                ->whereStatus('A')
                ->whereNotNull('email')
                ->orderBy('first_name')
                ->withAndWhereHas('branch', function ($query) use ($branchId) {
                    $query->where('id', '=', $branchId)->where('status', '=', 'A');
                })->get();
    }

    /**
     * Gets client for input dropdown list.
     *
     * @param mixed $tenantId
     * @return \Illuminate\Support\Collection
     */
    public function getClientAsList($branchId)
    {
        return $this->getClients($branchId)->pluck('name', 'id');
    }
}
