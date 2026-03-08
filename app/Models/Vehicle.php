<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\CompanyScope;
use App\Models\Traits\BelongsToCompany;



class Vehicle extends Model
{
    use SoftDeletes;
    use BelongsToCompany;


    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
    }

    protected $fillable = [
        'owner_id',
        'plate_number',
        'brand',
        'model',
        'current_mileage',
        'status',
        'company_id',
        'created_by',
    ];

    protected $casts = [
        'owner_id'        => 'integer',
        'current_mileage' => 'integer',
        'status'          => 'integer',
    ];

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function details()
    {
        return $this->hasOne(VehicleDetail::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
