<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $table = 'clients';

    protected $fillable = [
        'user_id',
        'created_at'

    ];

    // RELACIÓN: Un cliente pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // RELACIÓN: Un cliente tiene varias direcciones
    public function addresses()
    {
        return $this->hasMany(ClientAddress::class);
    }

    // Dirección principal
    public function primaryAddress()
    {
        return $this->hasOne(ClientAddress::class)->where('is_primary', true);
    }
}
