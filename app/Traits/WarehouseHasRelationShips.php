<?php

namespace Logistics\Traits;

use Carbon\Carbon;
use Illuminate\Support\Fluent;
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

    public function getManualIdDspAttribute()
    {
        return str_pad($this->manual_id, 2, '0', STR_PAD_LEFT);
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

        if ($request->manual_id) {
            $using = array_merge($using, ['manual_id' => $request->manual_id]);
        } else {
            $max = $tenant->invoices()
                //->where('branch_id', $request->branch_to)
                ->max('manual_id');

            if (!$max) {
                $max = 0;
            }

            $using = array_merge($using, ['manual_id' => $max + 1]);
        }

        [$year, $month, $day]  = array_map('intval', explode('-', request('created_at', date('Y-m-d'))));
        
        $invoice = $this->invoice()->updateOrCreate(
            ['id' => $request->invoice_id, 'tenant_id' => $this->tenant_id, 'warehouse_id' => $this->id, ],
            [
                'tenant_id' => $this->tenant_id,
                'warehouse_id' => $this->id,
                'client_id' => $this->client_id,
                'branch_id' => $request->branch_to,
                'client_name' => $request->client_name,
                'client_email' => $request->client_email,
                'volumetric_weight' => $request->total_volumetric_weight ? $request->total_volumetric_weight : 0,
                'real_weight' => $request->total_real_weight ? $request->total_real_weight : 0,
                'cubic_feet' => $request->total_cubic_feet ? $request->total_real_weight : 0,
                'total' => $request->total,
                'notes' => $request->notes,
                'created_at' => Carbon::create($year, $month, $day),
            ] + $using
        );

        $details = $request->invoice_detail ?: [];

        foreach ($details as $data) {
            $input = new Fluent($data);

            $qty = $input->qty;

            $invoice->details()->updateOrCreate(['id' => $input->wdid, ], [
                'qty' => $qty,
                'type' => $input->type,
                'length' => $input->length,
                'width' => $input->width,
                'height' => $input->height,
                'vol_weight' => $input->vol_weight,
                'real_weight' => $input->real_weight,
                'real_price' => $input->real_price ? $input->real_price : 0,
                'vol_price' => $input->vol_price ? $input->vol_price : 0,
                'total' => $this->getTotal($request, $input),
                'is_dhll' => isset($input->is_dhll),
                'tracking' => $input->tracking,
            ]);
        }

        $branch = $client->branch;
        $this->toBranch->notify(new WarehouseActivity($this->created_at, $this->id, "{$branch->code}{$client->manual_id_dsp}", $invoice->id, auth()->user()->full_name));

        if ($invoice) {
            if ($client->email !== $tenant->email_allowed_dup) {
                dispatch(new SendInvoiceCreatedEmail($tenant, $invoice));
            }
        }
    }
    
    public function getTotal($request, $input)
    {
        $total = 0;

        if ($request->has('chk_t_real_weight')) {
            $total = $input->real_weight * $input->real_price;
        } elseif ($request->has('chk_t_volumetric_weight')) {
            $total = $input->vol_weight * $input->vol_price;
        } elseif ($request->has('chk_t_cubic_feet')) {
        }

        return $total;
    }
}
