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
        'branch_from', 'branch_to','mailer_id','trackings','reference','qty', 'created_by_code', 'tenant_id', 'updated_by_code',
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

    public function genInvoice($request)
    {
        $invoice = Invoice::create([
            'tenant_id' => $this->tenant_id,
            'warehouse_id' => $this->id,
            'branch_id' => $request->branch_to,
            'client_name' => $request->client_name,
            'client_email' => $request->client_email,
            'volumetric_weight' => $request->volumetric_weight,
            'real_weight' => $request->real_weight,
            'total' => $request->total,
            'notes' => $request->notes,
        ]);

        foreach ($request->invoice_detail as $data) {
            $input = new Fluent($data);

            $volWeight = ($input->length && $input->width && $input->height) ? ($input->length * $input->width * $input->height) / 139 : 0;
            $whole = intval($volWeight);
            $dec = $volWeight - $whole;
            
            if ($dec > 0) {
                $volWeight = $whole + 1;
            }

            $invoice->details()->create([
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
