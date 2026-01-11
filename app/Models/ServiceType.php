<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'category',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
