<?php

namespace Logistics\DB;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'tenant_id', 'is_main_admin', 'status', 'type', 'avatar',
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
        static::creating(function ($user) {
            $user->token = str_random(30);
        });
        
        parent::boot();
    }

    public function branches()
    {
        return $this->belongsToMany(\Logistics\DB\Tenant\Branch::class);
    }

    public function company()
    {
        return $this->belongsTo(\Logistics\DB\Tenant\Tenant::class, 'tenant_id', 'id');
    }

    public function isAdmin()
    {
        return $this->type === 'A';
    }
}
