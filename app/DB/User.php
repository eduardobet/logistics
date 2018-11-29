<?php

namespace Logistics\DB;

use Logistics\Traits\UserBranch;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Logistics\Notifications\Tenant\ResetPwdNotification;

class User extends Authenticatable
{
    use Notifiable, UserBranch;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'tenant_id', 'is_main_admin', 'status', 'type', 'avatar',
        'full_name', 'pid', 'telephones', 'position', 'notes', 'address', 'created_by_code', 'updated_by_code',
        'permissions',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_main_admin' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($user) {
            $user->token = str_random(30);
        });

        static::saved(function ($user) {
            __do_forget_cache(__class__, ["employee.branches.{$user->id}"], []);
        });
    }

    /**
     * Format user permissions.
     *
     * @param array $permissions
     */
    public function setPermissionsAttribute($permissions)
    {
        $data = is_null($permissions) || $permissions == '[]' ? [] : $permissions;

        $this->attributes['permissions'] = json_encode($data);
    }

    /**
     * [getSettingsAttribute description]
     *
     * @param  string $value
     * @return Array
     */
    public function getPermissionsAttribute($value)
    {
        return $value && $value != 'null' ? json_decode($value, true) : [];
    }

    public function branches()
    {
        return $this->belongsToMany(\Logistics\DB\Tenant\Branch::class);
    }

    public function company()
    {
        return $this->belongsTo(\Logistics\DB\Tenant\Tenant::class, 'tenant_id', 'id');
    }

    public function branchesForInvoice()
    {
        return $this->belongsToMany(\Logistics\DB\Tenant\Branch::class, 'branch_for_invoice');
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function isAdmin()
    {
        return $this->type === 'A';
    }

    public function isSuperAdmin()
    {
        return in_array($this->position, [1, 2]);
    }

    /**
     * Send password reset notification
     *
     * @param  string $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPwdNotification($token));
    }
}
