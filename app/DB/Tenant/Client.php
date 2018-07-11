<?php

namespace Logistics\DB\Tenant;

use Logistics\Traits\Tenant;
use Illuminate\Support\Fluent;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use Tenant;

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

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['full_name'];

    /**
     * Boot the model
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            $branch = request('branch_id');
            $keys = ["clients.tenant.{$model->tenant_id}", "clients.tenant.{$model->tenant_id}.branch.{$branch}", ];

            __do_forget_cache(__class__, $keys, []);
        });
    }

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

    public function scopeWithAndWhereHas($query, $relation, $constraint)
    {
        return $query->whereHas($relation, $constraint)
            ->with([$relation => $constraint]);
    }

    public function getClientsByBranch($branchId)
    {
        $tenant = $this->getTenant();
        $key = "clients.tenant.{$tenant->id}.branch.{$branchId}";

        $clients = cache()->get($key, function () use ($tenant, $key, $branchId) {
            $clients = $tenant->clients()
                ->whereStatus('A')
                ->orderBy('first_name')
                ->withAndWhereHas('boxes', function ($query) use ($branchId) {
                    $query->where('branch_id', '=', $branchId)->where('status', '=', 'A');
                })->get();

            cache()->forever($key, $clients);

            return $clients;
        });

        return $clients;
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
