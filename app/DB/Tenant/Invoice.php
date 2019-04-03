<?php

namespace Logistics\DB\Tenant;

use Logistics\DB\User;
use Logistics\DB\Tenant\Payment;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Invoice extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'created_by_code', 'tenant_id', 'updated_by_code', 'branch_id', 'client_name', 'client_email', 'status', 'volumetric_weight', 'real_weight', 'total','notes', 'warehouse_id', 'client_id', 'is_paid', 'i_using', 'cubic_feet', 'fine_total', 'fine_ref', 'created_at', 'manual_id', 'delivered_trackings',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_paid' => 'boolean',
        'manual_id' => 'integer',
    ];

    /**
     * Attributes to include in the Audit.
     *
     * @var array
     */
    protected $auditInclude = [
        'volumetric_weight',
        'real_weight',
        'total',
        'notes',
        'is_paid',
        'i_using',
        'fine_total',
        'fine_ref',
        'delivered_trackings',
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

        static::saved(function ($model) {
            $keys = ["invoices.tenant.{$model->tenant_id}"];

            __do_forget_cache(__class__, $keys, []);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function transformAudit(array $data): array
    {
        $data['tenant_id'] = $this->tenant_id;
        
        return $data;
    }

    public function getManualIdDspAttribute()
    {
        return str_pad($this->manual_id, 2, '0', STR_PAD_LEFT);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'A');
    }

    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    public function details()
    {
        return $this->hasMany(InvoiceDetail::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_code')
            ->select('id', 'first_name', 'last_name');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by_code')
            ->select('id', 'first_name', 'last_name');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function scopeWithAndWhereHas($query, $relation, $constraint)
    {
        return $query->whereHas($relation, $constraint)
            ->with([$relation => $constraint]);
    }
}
