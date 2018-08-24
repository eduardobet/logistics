<?php

namespace Logistics\Traits;

use Illuminate\Support\Fluent;
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

    public function scopeWithAndWhereHas($query, $relation, $constraint)
    {
        return $query->whereHas($relation, $constraint)
            ->with([$relation => $constraint]);
    }

    public function genInvoice($request)
    {
        $client = $this->client()->find($this->client_id);

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
                'total' => $request->total,
                'notes' => $request->notes,
            ]
        );

        $datails = $request->invoice_detail ?: [];

        foreach ($datails as $data) {
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
    }
}
