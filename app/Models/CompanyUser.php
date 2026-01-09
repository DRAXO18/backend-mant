<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyUser extends Model
{
    protected $table = 'company_users';

    protected $fillable = [
        'company_id',
        'user_id',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /* ===========================
     | RELACIONES
     =========================== */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function roles()
    {
        return $this->belongsToMany(
            CompanyRole::class,
            'company_role_user',
            'company_user_id',
            'company_role_id'
        );
    }
}
