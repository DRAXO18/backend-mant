<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\BelongsToCompany;

class Technician extends Model
{
    use SoftDeletes;
    use BelongsToCompany;

    protected $fillable = [
        'user_id',
        'company_id',
        'specialty',
        'license_number',
        'experience_years',
        'active'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'company_id' => 'integer',
        'experience_years' => 'integer',
        'active' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}