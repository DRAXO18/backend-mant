<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Technician extends Model
{
    protected $table = 'technicians';

    protected $fillable = [
        'user_id',
        'company_id',
        'specialization',
        'experience_years',
        'verified',
        'status',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'experience_years' => 'integer',
        'status' => 'integer',
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

    /* ===========================
     | HELPERS
     =========================== */

    public function isActive(): bool
    {
        return $this->status === 1;
    }

    public function isVerified(): bool
    {
        return $this->verified === true;
    }
}
