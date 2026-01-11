<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceDetail extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'service_id';
    public $incrementing = false;

    protected $fillable = [
        'service_id',
        'observations',
        'recommendation',
    ];

    protected $casts = [
        'service_id' => 'integer',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}

