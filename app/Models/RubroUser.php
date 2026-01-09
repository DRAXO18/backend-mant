<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RubroUser extends Model
{
    protected $table = 'rubro_users';

    protected $fillable = [
        'user_id',
        'username',
        'position',
        'appointed_at',
        'status',
    ];

    protected $casts = [
        'appointed_at' => 'datetime',
        'status' => 'integer',
    ];

    /* ===========================
     | RELACIONES
     =========================== */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* ===========================
     | HELPERS
     =========================== */

    public function isActive(): bool
    {
        return $this->status === 1;
    }
}
