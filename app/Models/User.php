<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

use App\Models\Client;
use App\Models\CompanyUser;
use App\Models\Technician;
use App\Models\RubroUser;

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

     /* ===========================
     | RELACIONES DE PANELES
     =========================== */

    // Panel Cliente
    public function client()
    {
        return $this->hasOne(Client::class);
    }

    // Panel Empresa (admins)
    public function companyUsers()
    {
        return $this->hasMany(CompanyUser::class);
    }

    // Panel Empresa (técnicos)
    public function technicians()
    {
        return $this->hasMany(Technician::class);
    }

    // Panel Rubro (admins del sistema)
    public function rubroUser()
    {
        return $this->hasOne(RubroUser::class);
    }
}
