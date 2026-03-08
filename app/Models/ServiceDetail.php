<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\CompanyScope;
use App\Models\Traits\BelongsToCompany;



class ServiceDetail extends Model
{
    use SoftDeletes;

    use BelongsToCompany;

    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
    }

    protected $primaryKey = 'service_id';
    public $incrementing = false;

    protected $fillable = [
        'service_id',
        'observations',
        'recommendation',
        'company_id',
    ];

    protected $casts = [
        'service_id' => 'integer',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
