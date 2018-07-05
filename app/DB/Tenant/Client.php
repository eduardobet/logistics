<?php

namespace Logistics\DB\Tenant;

use Illuminate\Support\Fluent;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'tenant_id', 'status', 'type', 'telephones', 'created_by_code', 'updated_by_code', 'pid',
        'org_name', 'country_id', 'department_id', 'city_id', 'notes', 'pay_volume', 'special_rate', 'special_maritime', 'address',
        'vol_price', 'real_price',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'pay_volume' => 'boolean',
        'special_rate' => 'boolean',
        'special_maritime' => 'boolean',
    ];

    public function boxes()
    {
        return $this->hasMany(\Logistics\DB\Tenant\Box::class);
    }

    public function extraContacts()
    {
        return $this->hasMany(\Logistics\DB\Tenant\ExtraContact::class);
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

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function genBox($branchId, $branchCode)
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
        ]);
    }
}
