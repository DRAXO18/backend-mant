<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyRole extends Model
{
    protected $table = 'company_roles';

    protected $fillable = [
        'company_id',
        'name',
        'slug',
    ];

    protected $casts = [
        'company_id' => 'integer',
    ];

    /* ===========================
     | RELACIONES
     =========================== */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Usuarios de empresa que tienen este rol (via pivot)
    public function companyUsers()
    {
        return $this->belongsToMany(
            CompanyUser::class,
            'company_role_user',
            'company_role_id',
            'company_user_id'
        )->withTimestamps();
    }
}
