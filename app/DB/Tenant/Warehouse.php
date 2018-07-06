<?php

namespace Logistics\DB\Tenant;

use Illuminate\Support\Fluent;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'branch_from', 'branch_to','mailer_id','trackings','reference','qty', 'created_by_code', 'tenant_id', 'updated_by_code', 'client_id',
        'type',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($query) {
            $query->created_by_code = auth()->id();
        });

        static::updating(function ($query) {
            $query->updated_by_code = auth()->id();
        });
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'branch_from');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'branch_to');
    }

    public function genInvoice($request)
    {
        $client = $this->client()->find($this->client_id);

        $total = 0;

        if ($client->special_rate) {
            $total = $client->real_price * $request->total_real_weight;
        } elseif ($client->pay_volume) {
            $total = $client->vol_price * $request->total_volumetric_weight;
        } else {
            $branch = $this->toBranch()->find($request->branch_to);
            $total = ($request->is_dhl ? $branch->dhl_price : $branch->real_price) * $request->total_real_weight;
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
                'total' => $total,
                'notes' => $request->notes,
            ]
        );

        foreach ($request->invoice_detail as $data) {
            $input = new Fluent($data);

            $volWeight = ($input->length && $input->width && $input->height) ? ($input->length * $input->width * $input->height) / 139 : 0;
            $whole = intval($volWeight);
            $dec = $volWeight - $whole;
            
            if ($dec > 0) {
                $volWeight = $whole + 1;
            }

            $invoice->details()->updateOrCreate(['id' => $input->wdid, ], [
                'qty' => $input->qty,
                'type' => $input->type,
                'length' => $input->length,
                'width' => $input->width,
                'height' => $input->height,
                'vol_weight' => $volWeight,
                'real_weight' => $input->real_weight,
            ]);
        }
    }
}
