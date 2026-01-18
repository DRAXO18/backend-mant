<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;
use App\Models\UserIdentification;
use App\Models\Client;
use App\Models\Owner;
use App\Models\Admin;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * Campos permitidos para asignación masiva
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'status',
        'supabase_user_id',
        'google_id',
        'avatar',
        'email_verified_at',
    ];

    /**
     * Campos ocultos en respuestas JSON
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts de tipos automáticos
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // RELACIÓN CON SEGURIDAD

    // public function security()
    // {
    //     return $this->hasOne(UserSecurity::class);
    // }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function identifications()
    {
        return $this->hasMany(UserIdentification::class);
    }

    public function client()
    {
        return $this->hasOne(Client::class);
    }

    public function owner()
    {
        return $this->hasOne(Owner::class);
    }

    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_user')
            ->withPivot(['status'])
            ->withTimestamps();
    }
}
