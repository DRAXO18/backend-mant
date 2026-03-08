<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\CompanyScope;
use App\Models\Traits\BelongsToCompany;



class ServiceType extends Model
{
    use SoftDeletes;
    use BelongsToCompany;


    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
    }

    protected $fillable = [
        'name',
        'description',
        'category',
        'status',
        'company_id',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
