<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdentificationType extends Model
{
    use HasFactory;

    protected $table = 'identification_types';

    protected $fillable = [
        'name',
        'code',
    ];

    // RELACIÓN (1 tipo → muchos documentos)
    public function userIdentifications()
    {
        return $this->hasMany(UserIdentification::class);
    }
}
