<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientAddress extends Model
{
    use HasFactory;

    protected $table = 'client_addresses';

    protected $fillable = [
        'client_id',
        'ubigeo_id',
        'address_line',
        'reference',
        'latitude',
        'longitude',
        'is_primary',
        'status',
    ];

    // RELACIÓN: dirección → cliente
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // RELACIÓN: dirección → zona geográfica
    public function ubigeo()
    {
        return $this->belongsTo(Ubigeo::class);
    }
}
