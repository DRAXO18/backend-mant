<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleDetail extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'vehicle_id';
    public $incrementing = false;

    protected $fillable = [
        'vehicle_id',
        'usage_type',
        'has_gnv',
        'has_glp',
        'weekly_mileage',
        'notes',
    ];

    protected $casts = [
        'vehicle_id'     => 'integer',
        'has_gnv'        => 'boolean',
        'has_glp'        => 'boolean',
        'weekly_mileage'=> 'integer',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
