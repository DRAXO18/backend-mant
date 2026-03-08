<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\CompanyScope;
use App\Models\Traits\BelongsToCompany;



class VehicleDetail extends Model
{
    use SoftDeletes;
    use BelongsToCompany;


    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
    }

    protected $primaryKey = 'vehicle_id';
    public $incrementing = false;

    protected $fillable = [
        'vehicle_id',
        'usage_type',
        'has_gnv',
        'has_glp',
        'weekly_mileage',
        'notes',
        'company_id',

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
