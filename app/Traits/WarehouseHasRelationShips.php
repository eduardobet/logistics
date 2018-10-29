<?php

namespace Logistics\Traits;

use Illuminate\Support\Fluent;
use Logistics\Mail\Tenant\InvoiceCreated;
use Logistics\Jobs\Tenant\SendInvoiceCreatedEmail;
use Logistics\Notifications\Tenant\WarehouseActivity;

trait WarehouseHasRelationShips
{
    /**
     * Get the created at date for human.
     *
     * @return string
     */
    public function getCreatedAtAgoAttribute()
    {
        return do_diff_for_humans($this->created_at);
    }

    /**
     * Get created at for display.
     *
     * @param  string  $value
     * @return string
     */
    public function getCreatedAtDspAttribute($value)
    {
        return $this->created_at->format('d-m-Y H:i a');
    }

    public function invoice()
    {
        return $this->hasOne(\Logistics\DB\Tenant\Invoice::class);
    }

    public function client()
    {
        return $this->belongsTo(\Logistics\DB\Tenant\Client::class);
    }

    public function fromBranch()
    {
        return $this->belongsTo(\Logistics\DB\Tenant\Branch::class, 'branch_from');
    }

    public function toBranch()
    {
        return $this->belongsTo(\Logistics\DB\Tenant\Branch::class, 'branch_to');
    }

    public function creator()
    {
        return $this->belongsTo(\Logistics\DB\User::class, 'created_by_code')
            ->select('id', 'first_name', 'last_name');
    }

    public function editor()
    {
        return $this->belongsTo(\Logistics\DB\User::class, 'updated_by_code')
            ->select('id', 'first_name', 'last_name');
    }

    public function scopeWithAndWhereHas($query, $relation, $constraint)
    {
        return $query->whereHas($relation, $constraint)
            ->with([$relation => $constraint]);
    }

    public function genInvoice($request, $tenant)
    {
        $client = $this->client()->find($this->client_id);

        $using = [];

        if ($request->has('chk_t_real_weight')) {
            $using = ['i_using' => 'R'];
        } elseif ($request->has('chk_t_volumetric_weight')) {
            $using = ['i_using' => 'V'];
        } elseif ($request->has('chk_t_cubic_feet')) {
            $using = ['i_using' => 'C'];
        }

        $invoice = $this->invoice()->updateOrCreate(
            ['id' => $request->invoice_id, 'tenant_id' => $this->tenant_id, 'warehouse_id' => $this->id, ],
            [
                'tenant_id' => $this->tenant_id,
                'warehouse_id' => $this->id,
                'client_id' => $this->client_id,
                'branch_id' => $request->branch_to,
                'client_name' => $request->client_name,
                'client_email' => $request->client_email,
                'volumetric_weight' => $request->total_volumetric_weight,
                'real_weight' => $request->total_real_weight,
                'cubic_feet' => $request->total_cubic_feet,
                'total' => $request->total,
                'notes' => $request->notes,
            ] + $using
        );

        $details = $request->invoice_detail ?: [];

        foreach ($details as $data) {
            $input = new Fluent($data);

            $invoice->details()->updateOrCreate(['id' => $input->wdid, ], [
                'qty' => $input->qty,
                'type' => $input->type,
                'length' => $input->length,
                'width' => $input->width,
                'height' => $input->height,
                'vol_weight' => $input->vol_weight,
                'real_weight' => $input->real_weight,
                'is_dhll' => isset($input->is_dhll),
            ]);
        }

        $box = $client->boxes()->active()->first();
        $this->toBranch->notify(new WarehouseActivity($this->created_at, $this->id, "{$box->branch_code}{$client->id}", $invoice->id, auth()->user()->full_name));

        if ($invoice) {
            dispatch(new SendInvoiceCreatedEmail($tenant, $invoice));
        }
    }
}
