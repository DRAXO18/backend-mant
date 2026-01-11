<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'service_type_id',
        'client_id',
        'service_date',
        'mileage_at_service',
        'status',
    ];

    protected $casts = [
        'vehicle_id'        => 'integer',
        'service_type_id'   => 'integer',
        'client_id'         => 'integer',
        'service_date'      => 'datetime',
        'mileage_at_service'=> 'integer',
        'status'            => 'integer',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function details()
    {
        return $this->hasOne(ServiceDetail::class);
    }

    public function parts()
    {
        return $this->belongsToMany(
            Part::class,
            'service_parts'
        )->withPivot('quantity')
         ->withTimestamps();
    }
}

