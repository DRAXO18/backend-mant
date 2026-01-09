<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'companies';

    protected $fillable = [
        'name',
        'ruc',
        'ubigeo_id',
        'phone',
        'email',
        'status',
        'approval_status',
        'approved_at',
        'suspended_at',
    ];

    protected $casts = [
        'status' => 'string',
        'ubigeo_id' => 'integer',
    ];

    /* ===========================
     | RELACIONES
     =========================== */

    public function ubigeo()
    {
        return $this->belongsTo(Ubigeo::class);
    }

    // Usuarios afiliados a la empresa (admins/colaboradores/etc.)
    public function companyUsers()
    {
        return $this->hasMany(CompanyUser::class);
    }

    public function reviews()
    {
        return $this->hasMany(CompanyReview::class);
    }


    // Roles internos que esta empresa define
    public function roles()
    {
        return $this->hasMany(CompanyRole::class);
    }

    // Técnicos de esta empresa
    public function technicians()
    {
        return $this->hasMany(Technician::class);
    }

    /* ===========================
     | HELPERS
     =========================== */

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }
}
