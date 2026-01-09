<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserIdentification extends Model
{
    use HasFactory;

    protected $table = 'user_identifications';

    protected $fillable = [
        'user_id',
        'identification_type_id',
        'number_hash',
        'number_encrypted',
        'issued_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'issued_at'  => 'date',
            'expires_at' => 'date',
        ];
    }

    // RELACIÓN: Pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // RELACIÓN: Pertenece a un tipo de documento
    public function identificationType()
    {
        return $this->belongsTo(IdentificationType::class);
    }

    // MÉTODO OPCIONAL: Obtener el número real desencriptado
    public function getRealNumber(): string
    {
        return decrypt($this->number_encrypted);
    }
}
